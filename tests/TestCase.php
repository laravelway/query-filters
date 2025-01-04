<?php

declare(strict_types=1);

namespace LaravelWay\QueryFilters\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use LaravelWay\QueryFilters\QueryFiltersServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            static fn (string $modelName) => 'LaravelWay\\QueryFilters\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    final public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');

        $migration = include __DIR__.'/../database/migrations/create_users_table.php.stub';
        $migration->up();

    }

    protected function getPackageProviders($app): array
    {
        return [
            QueryFiltersServiceProvider::class,
        ];
    }
}
