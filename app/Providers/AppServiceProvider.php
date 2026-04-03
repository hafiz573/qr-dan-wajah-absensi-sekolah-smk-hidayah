<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            if (class_exists(\App\Models\Setting::class) && \Schema::hasTable('settings')) {
                $timezone = \App\Models\Setting::get('timezone', 'Asia/Jakarta');
                date_default_timezone_set($timezone);
                config(['app.timezone' => $timezone]);
            }
        } catch (\Exception $e) {
            // Table might not exist during migration
        }
    }
}
