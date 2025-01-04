<?php

declare(strict_types=1);

namespace LaravelWay\QueryFilters\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Exact same value
 *
 * Example: ?filters[trashed]=only #in case of search only trashed rows
 * Example: ?filters[trashed]=with #in case of search with trashed rows
 *
 * public array $filters = [
 *  'trashed' => TrashedFilter::class
 * ];
 */
final class TrashedFilter implements IFilter
{
    /**
     * @param  Builder<Model>  $builder
     * @param  string  $value
     */
    public function handle(Builder $builder, string $key, mixed $value, mixed $params = null): void
    {
        if (! in_array(SoftDeletes::class, class_uses($builder->getModel()), true)) {
            return;
        }

        if ($value === 'only') {
            $builder->onlyTrashed(); // @phpstan-ignore-line

            return;
        }

        if ($value === 'with') {
            $builder->withTrashed(); // @phpstan-ignore-line
        }
    }
}
