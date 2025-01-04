<?php

declare(strict_types=1);

namespace LaravelWay\QueryFilters\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Exact same value
 *
 * Example: ?filters[name]=John
 *
 * public array $filters = [
 *  'name' => EqualFilter::class
 * ];
 */
final class EqualFilter implements IFilter
{
    /**
     * @param  Builder<Model>  $builder
     */
    public function handle(Builder $builder, string $key, mixed $value, mixed $params = null): void
    {
        $builder->where($key, $value);
    }
}
