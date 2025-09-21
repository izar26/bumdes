<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\View\Components\ConfirmModal;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;
use App\Models\Bungdes;
use App\Models\Anggota;
use Illuminate\Support\Facades\Route;
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
        Blade::component('akun-row', \App\View\Components\AkunRow::class);
        Blade::component('confirm-modal', ConfirmModal::class);
        Paginator::useBootstrapFour();

        // Menggunakan View::composer untuk membagikan data ke semua view
        View::composer('*', function ($view) {
            $user = Auth::user();

            $photo = $user?->anggota?->photo ? asset('storage/' . $user->anggota->photo) : asset('vendor/adminlte/dist/img/avatar.png');

            $bungdes = Bungdes::first();

            $view->with('logo_bungdes', $bungdes ? asset('storage/' . $bungdes->logo) : asset('path/ke/logo/default.png')); // Perbaiki sintaks dan tambahkan default
            $view->with('logo_img', $photo);
        });
            Route::bind('anggota', function ($value) {
            return Anggota::where('anggota_id', $value)->firstOrFail();
        });
        // Schema::disableForeignKeyConstraints();`
    }
}
