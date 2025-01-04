<?php

declare(strict_types=1);

namespace LaravelWay\QueryFilters\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use LaravelWay\QueryFilters\Exceptions\FilterException;
use LaravelWay\QueryFilters\FiltersAbstract;

/**
 * Example: User::query()->addQueryFilters(UsersFilters::class)->get()
 *
 * Example: User::query()
 *     ->addQueryFilters(UsersFilters::class)
 *     ->addQueryFilters(CustomersFilters::class)
 *     ->get()
 *
 * Example: User::query()
 *      ->addQueryFilters([
 *          'name' => ContainsFilter::class,
 *          'email' => EqualsFilter::class,
 *      ])
 *      ->get()
 *
 * Example: User::query()
 *      ->addQueryFilters([
 *          'name' => function (Builder $builder, string $key, mixed $value, mixed $params = null) {
 *              $builder->where($key, $value);
 *          }
 *      ])
 *      ->get()
 */
trait QueryFilters
{
    private ?FiltersAbstract $filters = null;

    /**
     * @param  Builder<Model>  $builder
     * @param  class-string|array<string, class-string|callable|array{0: class-string, 1: mixed}>  $filters
     * @return Builder<Model>
     *
     * @throws FilterException
     */
    public function scopeAddQueryFilters(Builder $builder, string|array $filters, ?Request $request = null): Builder
    {
        /** @var array<string, mixed> $query */
        $query = ($request ?: request())->query(null, []);

        if (! $this->filters) {
            $this->filters = $this->makeFilters($filters, $query);
        }

        $this->filters->add($builder, is_array($filters) ? $filters : $this->makeFilters($filters, $query)->getAvailableFilters());

        return $builder;
    }

    /**
     * @param  class-string|array<string, class-string|callable|array{0: class-string, 1: mixed}>  $filters
     * @param  array<string, mixed>  $queryData
     *
     * @throws FilterException
     */
    private function makeFilters(string|array $filters, array $queryData): FiltersAbstract
    {
        if (is_array($filters)) {
            return new class($queryData) extends FiltersAbstract {};
        }

        if (! class_exists($filters)) {
            throw new FilterException(sprintf("Filter class %s doesn't exists.", $filters), 400);
        }

        $result = new $filters($queryData);

        if (! $result instanceof FiltersAbstract) {
            throw new FilterException(sprintf('Filter class %s should be extended from FiltersAbstract.', $filters), 400);
        }

        return $result;
    }
}
