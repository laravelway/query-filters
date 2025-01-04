<?php

declare(strict_types=1);

namespace LaravelWay\QueryFilters\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use LaravelWay\QueryFilters\Exceptions\FilterException;

/**
 * We use this filter when we have 1 search param, but want to check in table's different fields
 *
 * Example: ?filters[search]=John
 *
 * public array $filters = [
 *  'search' => [ContainsFilter::class. ['first_name', 'last_name']]
 * ];
 */
final class ContainsInFilter implements IFilter
{
    /**
     * @param  Builder<Model>  $builder
     * @param  string  $value
     *
     * @throws FilterException
     */
    public function handle(Builder $builder, string $key, mixed $value, mixed $params = null): void
    {
        if (! is_array($params)) {
            throw new FilterException('The second parameter for ContainsInFilter should be array', 400);
        }

        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $fields = implode("|| ' ' || ", $params);
            $builder->whereRaw("($fields) like ?", ["%$value%"]);
        } else {
            // @codeCoverageIgnoreStart
            $fields = implode(", ' ', ", $params);
            $builder->whereRaw("CONCAT($fields) like ?", ["%$value%"]);
            // @codeCoverageIgnoreEnd
        }
    }
}
