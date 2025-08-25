<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\CakeDayService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CakeDayService::class, function () {
            return new CakeDayService;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
