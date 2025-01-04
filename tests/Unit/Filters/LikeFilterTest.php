<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use LaravelWay\QueryFilters\Filters\LikeFilter;
use LaravelWay\QueryFilters\Models\User;

mutates(LikeFilter::class);

test('testing LikeFilter', function (string $pattern, int $expectedCount) {
    User::factory(4)->sequence(
        ['name' => 'Demo'],
        ['name' => 'Alex'],
        ['name' => 'Demo1'],
        ['name' => 'Alex Demo'],
    )->create();

    $request = Request::create('/dummy-url', 'GET', ['name' => $pattern]);

    $query = User::query()->addQueryFilters(filters: [
        'name' => LikeFilter::class,
    ], request: $request);

    expect($query->count())
        ->toBe($expectedCount);
})->with([
    ['Demo', 1],
    ['Demo*', 2],
    ['*Demo', 2],
    ['other_value', 0],
]);
