<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use LaravelWay\QueryFilters\Exceptions\FilterException;
use LaravelWay\QueryFilters\Filters\ContainsFilter;
use LaravelWay\QueryFilters\Filters\ContainsInFilter;
use LaravelWay\QueryFilters\Filters\EqualFilter;
use LaravelWay\QueryFilters\FiltersAbstract;
use LaravelWay\QueryFilters\Models\User;

mutates(FiltersAbstract::class);

it("throws an exception if filter class doesn't exists", function () {
    $request = Request::create('/dummy-url', 'GET', ['name' => 'test']);

    // @phpstan-ignore-next-line
    User::query()->addQueryFilters(filters: [
        'name' => 'non_existed_class',
    ], request: $request);
})->throws(FilterException::class, "Filter class non_existed_class doesn't exists", 400);

it("throws an exception if filter class doesn't implement IFilter interface", function () {
    $request = Request::create('/dummy-url', 'GET', ['name' => 'test']);

    $class = new class
    {
        /**
         * @param  Builder<Model>  $builder
         */
        public function handle(Builder $builder, string $key, mixed $value, mixed $params = null): void {}
    };

    User::query()->addQueryFilters(filters: [
        'name' => get_class($class),
    ], request: $request);
})->throws(FilterException::class, 'should implement IFilter interface', 400);

it('returns all available filters in filters class', function () {
    $class = new class(['name' => 'Something']) extends FiltersAbstract
    {
        /** @var array<string, class-string|callable|array{0: class-string, 1: mixed}> */
        public array $filters = [
            'name' => EqualFilter::class,
        ];

        /**
         * @param  Builder<Model>  $builder
         */
        public function filterSearch(Builder $builder, string $key, mixed $value, mixed $params = null): void {}
    };

    expect(array_keys($class->getAvailableFilters()))->toBe([
        'name',
        'search',
    ]);
});

it('uses callbacks as filters', function () {
    User::factory(2)->sequence(
        ['name' => 'Alex'],
        ['name' => 'Liza']
    )->create();

    $request = Request::create('/dummy-url', 'GET', ['name' => 'Alex']);

    $query = User::query()
        ->addQueryFilters(filters: [
            'name' => function (Builder $builder, string $key, mixed $value, mixed $params = null) {
                $builder->where($key, $value);
            },
        ], request: $request);

    expect($query->count())
        ->toBe(1);
});

it('uses custom filtering functions in Filters classes', function () {
    User::factory(3)->sequence(
        ['name' => 'Alex'],
        ['name' => 'John'],
        ['name' => 'Liza']
    )->create();

    $request = Request::create('/dummy-url', 'GET', ['search' => 'Alex']);

    /** @var array<string, mixed> $query */
    $query = $request->query(null, []);

    $class = new class($query) extends FiltersAbstract
    {
        /**
         * @param  Builder<Model>  $builder
         */
        public function filterSearch(Builder $builder, string $key, mixed $value, mixed $params = null): void
        {
            $builder->where('name', 'Alex')
                ->orWhere('name', 'Liza');
        }
    };

    $query = User::query()
        ->addQueryFilters(filters: get_class($class), request: $request);

    expect($query->count())
        ->toBe(2);
});

it(
    'uses for filters only keys defined in filters list or as functions and ignores empty values in query string',
    /**
     * @throws ReflectionException
     */
    function () {

        $request = Request::create('/dummy-url', 'GET', [
            'name' => 'alex',
            'gender' => 'male',
            'search' => '',
        ]);

        /** @var array<string, mixed> $query */
        $query = $request->query(null, []);

        $class = new class($query) extends FiltersAbstract
        {
            /** @var array<string, class-string> */
            public array $filters = [
                'name' => EqualFilter::class,
                'age' => ContainsFilter::class,
            ];

            /**
             * @param  Builder<Model>  $builder
             */
            public function filterSearch(Builder $builder, string $key, mixed $value, mixed $params = null): void {}
        };

        // testing private method

        /** @var array $result */
        $result = testNotPublicMethod($class, 'getFilters', $class->getAvailableFilters()); // @phpstan-ignore-line

        expect($result)->toBe(['name']);

    });

it('throws an exception if filter is array but the params is not defined', function () {
    $request = Request::create('/dummy-url', 'GET', ['name' => 'alex']);

    User::query()
        ->addQueryFilters(filters: [ // @phpstan-ignore-line
            'name' => [ContainsInFilter::class],
        ], request: $request);
})->throws(FilterException::class, 'The second parameter in array must be defined', 400);
