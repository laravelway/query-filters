# Readme

This package can be used for filtering eloquent using query string. 

```
// URL example: https://example.com?name=John&email=gmail.com
```

First you have to use `QueryFilters` trait in your model.

```php

use LaravelWay\QueryFilters\Traits\QueryFilters;

class User extends Model
{
     use QueryFilters;
}
```

Then you can use `addQueryFilters` scope on your model to add any filters you want.

1. Using array of filters

```php
use LaravelWay\QueryFilters\Filters\EqualFilter;
use LaravelWay\QueryFilters\Filters\ContainsFilter;

User::query()
    ->addQueryFilters(filters: [
        'name' => EqualFilter::class,
        'email' => ContainsFilter::class
    ]);
```

In this case it will filter by name parameter and email parameter from query string. 

2. You can call `addQueryFilters` multiple times. It will merge all filters together. 

```php
use LaravelWay\QueryFilters\Filters\EqualFilter;
use LaravelWay\QueryFilters\Filters\ContainsFilter;

User::query()
    ->addQueryFilters(filters: [
        'name' => EqualFilter::class,
    ])
    ->addQueryFilters(filters: [
        'email' => ContainsFilter::class,
    ]);
```

3. You can use callbacks as filters.

```php
User::query()
    ->addQueryFilters(filters: [
        'name' => function (Builder $builder, string $key, mixed $value, mixed $params = null) {
            $builder->where($key, $value);
        },
    ]);
```

4. You can use filters classes to define all filters there. In that case class must be extended from `FiltersAbstract` class.  

```php

use LaravelWay\QueryFilters\Filters\EqualFilter;
use LaravelWay\QueryFilters\Filters\ContainsFilter;

class UserFilters extends FiltersAbstract 
{
    
    /** @var array<string, class-string|callable|array{0: class-string, 1: mixed}> */
    public array $filters = [
        'name' => EqualFilter::class,
        'email' => ContainsFilter::class,
    ];
    
    /**
     * @param  Builder<Model>  $builder
     */
    public function filterSearch(Builder $builder, string $key, mixed $value, mixed $params = null): void {
        $builder->whereLike('name', "%$value%")->whereLike('email', "%$value%");
    }
}

// and then you can use this class for filtering
User::query()->addQueryFilters(filters: UserFilters::class);
```

As you can see, you can define standard filters in `$filters` property as array, 
or you can define custom functions which are prefixed with `filter` word. 
In our case `filterSearch` is using as filtering function, it means that it will filter
by query parameter `search`. 

There are some standard filtering classes you can use for filtering. 

### EqualFilter

it filters for exact value.

```php
public array $filters = [
    'name' => EqualFilter::class,
];

// ?name=John - it will search all rows which are equals to John.
// it can be case-insensitive if table column's collation ends with _ci suffix. 
```

### ContainsFilter

It filters rows which are contains that value in any part of string.

```php
public array $filters = [
    'name' => ContainsFilter::class,
];

// ?name=John - it will search all rows which are contains John string.
// it can be case-insensitive if table column's collation ends with _ci suffix. 
```

### ContainsInFilter

This filter can be used if we need to filter for value can be found on multiple columns.
Usually it used then user types something in one search input, but we must filter rows 
where that value can be found on one of the following columns. 

```php
public array $filters = [
    'search' => [ContainsInFilter::class, ['name', 'email', 'role']],
];

// ?search=John - it will search all rows which are contains John string in name, email or role columns.
// it can be case-insensitive if table column's collation ends with _ci suffix. 
```


### LikeFilter

This filter is similar to ContainsFilter, but contains will find in any part of string, 
but with like filter, you can specify in query string via * which part of string sould contain a value. 

```php
public array $filters = [
    'name' => LikeFilter::class,
];

// ?name=John* - it will search all rows which are starting with John
// ?name=*John - it will search all rows which are ending with John
// it can be case-insensitive if table column's collation ends with _ci suffix. 
```

### TrashedFilter

This filter is using to filter deleted rows if model uses SoftDeletes trait. 

```php
public array $filters = [
    'trashed' => TrashedFilter::class,
];

// ?trashed=with - it will search all rows even soft deleted rows
// ?trashed=only - it will search only soft deleted rows
```

### DateFilter

This filter is using for filter dates. 

```php
public array $filters = [
    'trashed' => TrashedFilter::class,
];

// ?created_at=2024-03-01 - it will filter all rows which are created exactly at selected date  
// ?created_at=2024-03-01, - it will filter all rows where created_at field more or equal to selected date
// ?created_at=,2024-03-01 - it will filter all rows where created_at field less or equal to selected date
// ?created_at=2024-03-01,2024-03-16 - it will filter all rows where created_at field is between two selected dates including both dates
```

---

## TODO
- test sorting
- filter relations
- sort relations
- include relations into response
