<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\TokenServiceInterface;
use App\Services\LiveKitTokenService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(TokenServiceInterface::class, LiveKitTokenService::class);
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
