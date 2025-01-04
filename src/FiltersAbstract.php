<?php

declare(strict_types=1);

namespace LaravelWay\QueryFilters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use LaravelWay\QueryFilters\Exceptions\FilterException;
use LaravelWay\QueryFilters\Filters\IFilter;

abstract class FiltersAbstract
{
    /** @var array<string, class-string|callable|array{0: class-string, 1: mixed}> */
    public array $filters = [];

    /**
     * @param  array<string, mixed>  $queryData
     */
    public function __construct(protected array $queryData) {}

    /**
     * @param  Builder<Model>  $builder
     * @param  array<string, class-string|callable|array{0: class-string, 1: mixed}>  $filters
     * @return Builder<Model>
     *
     * @throws FilterException
     */
    final public function add(Builder $builder, array $filters): Builder
    {
        $this->filters = array_merge($this->filters, $filters);

        foreach ($this->getFilters($filters) as $key) {
            $filter = $this->resolveFilter($key);

            $params = null;

            if (is_array($this->filters[$key])) {
                if (! isset($this->filters[$key][1])) {
                    throw new FilterException('The second parameter in array must be defined', 400);
                }

                $params = $this->filters[$key][1];
            }

            if (is_callable($filter)) {
                $filter($builder, $key, $this->queryData[$key], $params);
            } else {
                $filter->handle($builder, $key, $this->queryData[$key], $params);
            }

        }

        return $builder;
    }

    /**
     * It returns all classes defined in filters property or all customer filtering functions which are
     * defined in filters class which names are starting from filterSomething.
     *
     * @return array<string, class-string|callable|array{0: class-string, 1: mixed}>
     */
    final public function getAvailableFilters(): array
    {
        $filters = $this->filters;

        $filterableMethods = array_filter(
            get_class_methods(static::class),
            static fn (string $method) => Str::startsWith($method, 'filter')
        );

        $object = new static([]); // @phpstan-ignore-line

        foreach ($filterableMethods as $method) {
            $key = preg_replace('#^filter#', '', $method);

            if (! empty($key)) {
                $filters[mb_strtolower($key)] = [$object, $method];
            }
        }

        return $filters;
    }

    /**
     * @param  array<string, class-string|callable|array{0: class-string, 1: mixed}>  $filters
     * @return string[]
     */
    private function getFilters(array $filters): array
    {
        return array_keys(array_filter(Arr::only($this->queryData, array_keys($filters))));
    }

    /**
     * @throws FilterException
     */
    private function resolveFilter(string $key): IFilter|callable
    {
        $filter = $this->filters[$key];

        if (is_callable($filter)) {
            return $filter;
        }

        if (is_array($filter)) {
            $filter = $filter[0];
        }

        if (! class_exists($filter)) {
            throw new FilterException(sprintf("Filter class %s doesn't exists", $filter), 400);
        }

        $class = new $filter;

        if (! $class instanceof IFilter) {
            throw new FilterException(sprintf('Filter class %s should implement IFilter interface', $filter), 400);
        }

        return $class;
    }
}
