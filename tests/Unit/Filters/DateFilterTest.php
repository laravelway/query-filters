<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use LaravelWay\QueryFilters\Filters\DateFilter;
use LaravelWay\QueryFilters\Models\User;

mutates(DateFilter::class);

test('testing DateFilter', function ($date, $expectedCount) {
    User::factory(6)->sequence(
        ['created_at' => now()->subDays(4)],
        ['created_at' => now()->subDays(3)],
        ['created_at' => now()->subDays(2)],
        ['created_at' => now()->subDays(1)->toDateString().' 01:00:00'],
        ['created_at' => now()->subDays(1)->toDateString().' 23:00:00'],
        ['created_at' => now()],
    )->create();

    // @phpstan-ignore-next-line
    $date = match ($date) {
        'equals' => now()->subDays(3)->toDateString(),
        'more_than' => now()->subDays(3)->toDateString().',',
        'less_than' => ','.now()->subDays(1)->toDateString(),
        'between' => now()->subDays(2)->toDateString().','.now()->subDays(1)->toDateString(),
        'empty' => ','.now()->subDays(5)->toDateString(),
        'not_date' => 'bla',
        'datetime' => now()->subDays(3)->toDateString().' 01:00:00',
    };

    $request = Request::create('/dummy-url', 'GET', ['created_at' => $date]);

    $query = User::query()->addQueryFilters(filters: [
        'created_at' => DateFilter::class,
    ], request: $request);

    expect($query->count())
        ->toBe($expectedCount);
})->with([
    ['equals', 1],
    ['more_than', 5],
    ['less_than', 5],
    ['between', 3],
    ['empty', 0],
    ['not_date', 6],
    ['datetime', 1],
]);
