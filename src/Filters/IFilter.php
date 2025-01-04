<?php

declare(strict_types=1);

namespace LaravelWay\QueryFilters\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface IFilter
{
    /**
     * @param  Builder<Model>  $builder
     */
    public function handle(Builder $builder, string $key, mixed $value, mixed $params = null): void;
}
