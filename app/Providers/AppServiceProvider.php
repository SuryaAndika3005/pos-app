<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

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
        // FIX: Pastikan semua pencatatan waktu menggunakan WIB (Asia/Jakarta)
        // Tambahkan juga APP_TIMEZONE=Asia/Jakarta di file .env
        date_default_timezone_set(config('app.timezone', 'Asia/Jakarta'));
        Carbon::setLocale('id');
    }
}