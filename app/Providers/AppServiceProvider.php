<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register():void
    {
        $this->app->bind(
            \App\Repositories\BookRepository::class,
            \App\Repositories\EloquentBookRepository::class
        )

        $this->app->bind(
            \App\Services\BookService::class,
            function($app){
                return new \App\Services\BookService($app->make(\App\Repositories\BookRepository::class));
            }
        )
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
