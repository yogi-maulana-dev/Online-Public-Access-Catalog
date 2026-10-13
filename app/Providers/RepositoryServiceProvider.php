<?php

namespace App\Providers;

use App\Repositories\Contracts\BookRepositoryInterface;
use App\Repositories\EloquentBookRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        BookRepositoryInterface::class => EloquentBookRepository::class,
    ];
}
