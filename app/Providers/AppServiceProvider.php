<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View; // Import facade View
use Illuminate\Support\Facades\Auth;  // Import facade Auth
use App\View\Components\ConfirmModal;
use Illuminate\Support\Facades\Schema;;

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

    View::composer('*', function ($view) {
        $user = Auth::user();
        $photo = $user?->adminlte_image() ?? asset('vendor/adminlte/dist/img/avatar.png');

        $view->with('logo_img', $photo);
    });
    //   Schema::disableForeignKeyConstraints();
}

}
