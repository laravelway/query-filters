<?php

declare(strict_types=1);

namespace LaravelWay\QueryFilters\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Example: ?filters[created_at]=2024-01-01
 * Example: ?filters[created_at]=,2024-01-01 # only end date use "," at first
 * Example: ?filters[created_at]=2024-01-01, # only start date use "," at last
 * Example: ?filters[created_at]=2024-01-01,2024-01-31
 *
 * public array $filters = [
 *   'name' => DateFilter::class
 *  ];
 */
final class DateFilter implements IFilter
{
    /**
     * @param  Builder<Model>  $builder
     * @param  string  $value
     */
    public function handle(Builder $builder, string $key, mixed $value, mixed $params = null): void
    {
        $dates = explode(',', $value);

        foreach ($dates as $date) {
            if (! empty($date) && strtotime($date) === false) {
                return;
            }
        }

        $dates = array_map(static fn (mixed $date) => ! empty($date) ? Carbon::parse($date)->toDateString() : '', $dates);

        if (count($dates) > 1) {
            if (empty($dates[0])) {
                // less or equal than
                $builder->where($key, '<=', $dates[1].' 23:59:59');
            } elseif (empty($dates[1])) {
                // more or equal than
                $builder->where($key, '>=', $dates[0]);
            } else {
                // between two days
                $builder
                    ->where($key, '>=', $dates[0])
                    ->where($key, '<=', $dates[1].' 23:59:59');
            }
        } else {
            // exact date
            $builder->where($key, '>=', $dates[0])
                ->where($key, '<=', $dates[0].' 23:59:59');
        }
    }
}
