<?php

declare(strict_types=1);

namespace LaravelWay\QueryFilters\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Example: ?filters[name]=*John*
 * Example: ?filters[name]=John*
 *
 * public array $filters = [
 *  'name' => LikeFilter::class
 * ];
 */
final class LikeFilter implements IFilter
{
    /**
     * @param  Builder<Model>  $builder
     * @param  string  $value
     */
    public function handle(Builder $builder, string $key, mixed $value, mixed $params = null): void
    {
        $value = str_replace('*', '%', $value);

        $builder->whereLike($key, $value);
    }
}
