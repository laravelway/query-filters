<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use LaravelWay\QueryFilters\Filters\ContainsFilter;
use LaravelWay\QueryFilters\Models\User;

mutates(ContainsFilter::class);

test('testing ContainsFilter', function () {
    User::factory(4)->sequence(
        ['name' => 'Demo'],
        ['name' => 'Alex'],
        ['name' => 'Demo1'],
        ['name' => 'Alex Demo'],
    )->create();

    $request = Request::create('/dummy-url', 'GET', ['name' => 'demo']);

    $query = User::query()->addQueryFilters(filters: [
        'name' => ContainsFilter::class,
    ], request: $request);

    expect($query->count())
        ->toBe(3);
});
