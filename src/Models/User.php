<?php

declare(strict_types=1);

namespace LaravelWay\QueryFilters\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use LaravelWay\QueryFilters\Database\Factories\UserFactory;
use LaravelWay\QueryFilters\Traits\QueryFilters;

final class User extends Model
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use QueryFilters, SoftDeletes;
}
