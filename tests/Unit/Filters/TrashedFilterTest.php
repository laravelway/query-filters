<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use LaravelWay\QueryFilters\Filters\TrashedFilter;
use LaravelWay\QueryFilters\Models\User;
use LaravelWay\QueryFilters\Traits\QueryFilters;

mutates(TrashedFilter::class);

test('testing TrashedFilter', function (string $filter, int $expectedCount) {
    User::factory(5)->sequence(
        ['name' => 'Demo', 'deleted_at' => now()],
        ['name' => 'Alex'],
        ['name' => 'Liza'],
        ['name' => 'Demo1', 'deleted_at' => now()],
        ['name' => 'Alex Demo'],
    )->create();

    $request = Request::create('/dummy-url', 'GET', ['trashed' => $filter]);

    $query = User::query()->addQueryFilters(filters: [
        'trashed' => TrashedFilter::class,
    ], request: $request);

    expect($query->count())
        ->toBe($expectedCount);
})->with([
    ['with', 5],
    ['only', 2],
    ['something_else', 3],
]);

it('ignores trashing values if models not uses SoftDeletes trait', function (string $filter, int $expectedCount) {
    $class = new class extends Model
    {
        use QueryFilters;

        public function getTable(): string
        {
            return 'users';
        }
    };

    User::factory(2)->sequence(
        ['name' => 'Demo', 'deleted_at' => now()],
        ['name' => 'Liza'],
    )->create();

    $request = Request::create('/dummy-url', 'GET', ['trashed' => $filter]);

    $className = get_class($class);

    $query = $className::query()->addQueryFilters(filters: [
        'trashed' => TrashedFilter::class,
    ], request: $request);

    expect($query->count())
        ->toBe($expectedCount);
})->with([
    ['with', 2],
    ['only', 2],
    ['something_else', 2],
]);
