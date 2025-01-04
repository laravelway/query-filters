<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use LaravelWay\QueryFilters\Exceptions\FilterException;
use LaravelWay\QueryFilters\Filters\ContainsFilter;
use LaravelWay\QueryFilters\Filters\EqualFilter;
use LaravelWay\QueryFilters\FiltersAbstract;
use LaravelWay\QueryFilters\Models\User;
use LaravelWay\QueryFilters\Traits\QueryFilters;

mutates(QueryFilters::class);

test('if request is not specified it will get global request object', function () {
    User::factory(2)->sequence(
        ['name' => 'Alex'],
        ['name' => 'Liza']
    )->create();

    $request = request();
    $request->setMethod('GET');
    $request->merge(['name' => 'Alex']);

    $query = User::query()->addQueryFilters(filters: [
        'name' => EqualFilter::class,
    ]);

    expect($query->count())
        ->toBe(1);
});

it('can add filters as array', function () {
    User::factory(2)->sequence(
        ['name' => 'Alex'],
        ['name' => 'Liza']
    )->create();

    $request = Request::create('/dummy-url', 'GET', ['name' => 'Alex']);

    $query = User::query()
        ->addQueryFilters(filters: [
            'name' => EqualFilter::class,
        ], request: $request);

    expect($query->count())
        ->toBe(1);
});

it('can add filters as class', function () {
    User::factory(2)->sequence(
        ['name' => 'Alex'],
        ['name' => 'Liza']
    )->create();

    $request = Request::create('/dummy-url', 'GET', ['name' => 'Alex']);

    /** @var array<string, mixed> $query */
    $query = $request->query(null, []);

    $class = new class($query) extends FiltersAbstract
    {
        /** @var array<string, class-string|callable|array{0: class-string, 1: mixed}> */
        public array $filters = [
            'name' => EqualFilter::class,
        ];
    };

    $query = User::query()
        ->addQueryFilters(filters: get_class($class), request: $request);

    expect($query->count())
        ->toBe(1);
});

it("throws an exception if filter class doesn't exists", closure: function () {
    User::query()->addQueryFilters(filters: 'not_existed_class'); // @phpstan-ignore-line
})->throws(FilterException::class, "Filter class not_existed_class doesn't exists.", 400);

it("throws an exception if filter class doesn't extends from FiltersAbstract", closure: function () {
    $request = Request::create('/dummy-url', 'GET', ['name' => 'alex']);

    $class = new class
    {
        /** @var array<string, class-string|callable|array{0: class-string, 1: mixed}> */
        public array $filters = [
            'name' => EqualFilter::class,
        ];
    };

    User::query()->addQueryFilters(filters: get_class($class), request: $request);
})->throws(FilterException::class, 'should be extended from FiltersAbstract.', 400);

it('uses multiple filters at once', function () {
    User::factory()->create(['name' => 'Alex', 'email' => 'alex1-demo@gmail.com']);
    User::factory()->create(['name' => 'Alex', 'email' => 'alex2-demo@gmail.com']);
    User::factory()->create(['name' => 'Alex', 'email' => 'alex3@gmail.com']);

    $request = Request::create('/dummy-url', 'GET', ['name' => 'Alex', 'email' => 'demo']);

    $query = User::query()
        ->addQueryFilters(filters: [
            'name' => EqualFilter::class,
            'email' => ContainsFilter::class,
        ], request: $request);

    expect($query->count())
        ->toBe(2);
});

it('uses addQueryFilters function multiple times', function () {
    User::factory()->create(['name' => 'Alex', 'email' => 'alex1-demo@gmail.com']);
    User::factory()->create(['name' => 'Alex', 'email' => 'alex2-demo@gmail.com']);
    User::factory()->create(['name' => 'Alex', 'email' => 'alex3@gmail.com']);

    $request = Request::create('/dummy-url', 'GET', ['name' => 'Alex', 'email' => 'demo']);

    $query = User::query()
        ->addQueryFilters(filters: [
            'name' => EqualFilter::class,
        ], request: $request)
        ->addQueryFilters(filters: [
            'email' => ContainsFilter::class,
        ], request: $request);

    expect($query->count())
        ->toBe(2);
});
