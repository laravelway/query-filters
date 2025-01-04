<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use LaravelWay\QueryFilters\Exceptions\FilterException;
use LaravelWay\QueryFilters\Filters\ContainsInFilter;
use LaravelWay\QueryFilters\Models\User;

mutates(ContainsInFilter::class);

test('testing ContainsInFilter', function () {
    User::factory(4)->sequence(
        ['name' => 'Demo'],
        ['name' => 'Alex'],
        ['name' => 'Demo1'],
        ['name' => 'Alex', 'email' => 'demo-1@gmail.com'],
    )->create();

    $request = Request::create('/dummy-url', 'GET', ['search' => 'Demo']);

    $query = User::query()->addQueryFilters(filters: [
        'search' => [ContainsInFilter::class, ['name', 'email']],
    ], request: $request);

    expect($query->count())
        ->toBe(3);
});

it('throws an exception if second parameter for ContainsInFilter is not array', function () {
    $request = Request::create('/dummy-url', 'GET', ['search' => 'demo']);

    User::query()->addQueryFilters(filters: [
        'search' => [ContainsInFilter::class, 'not_array'],
    ], request: $request);
})->throws(FilterException::class, 'The second parameter for ContainsInFilter should be array', 400);
