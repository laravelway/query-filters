<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelWay\QueryFilters\Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)->in(__DIR__);

/**
 * @throws ReflectionException
 */
function testNotPublicMethod(object $instance, string $pMethodName, mixed ...$pParams): mixed
{
    $refObj = new ReflectionClass($instance);
    $method = $refObj->getMethod($pMethodName);
    $method->setAccessible(true);

    return $method->invoke($instance, ...$pParams);
}
