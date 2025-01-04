<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use LaravelWay\QueryFilters\Filters\EqualFilter;
use LaravelWay\QueryFilters\Models\User;

mutates(EqualFilter::class);

test('testing EqualsFilter', function () {
    User::factory(2)->sequence(
        ['name' => 'Demo'],
        ['name' => 'Demo1'],
    )->create();

    $request = Request::create('/dummy-url', 'GET', ['name' => 'Demo']);

    $query = User::query()->addQueryFilters(filters: [
        'name' => EqualFilter::class,
    ], request: $request);

    expect($query->count())
        ->toBe(1);
});
