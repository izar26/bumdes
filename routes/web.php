<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\BeritaController;
use App\Http\Controllers\Admin\PotensiController;
use App\Http\Controllers\Admin\ProfilController;
use App\Http\Controllers\Admin\HomepageSettingController;
use App\Http\Controllers\Admin\SocialLinkController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\BungdesController;
use App\Http\Controllers\Admin\UnitUsahaController;
use App\Http\Controllers\Admin\AkunController;
use App\Http\Controllers\Admin\UserController;


Route::get('/', [HomeController::class, 'index']);

Auth::routes();
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard'); // Nanti kita buat view ini
    })->name('dashboard');

    Route::resource('berita', BeritaController::class);
    Route::resource('potensi', PotensiController::class);
    Route::get('profil', [ProfilController::class, 'edit'])->name('profil.edit');
    Route::put('profil', [ProfilController::class, 'update'])->name('profil.update');
    Route::get('pengaturan-halaman', [HomepageSettingController::class, 'edit'])->name('homepage_setting.edit');
    Route::put('pengaturan-halaman', [HomepageSettingController::class, 'update'])->name('homepage_setting.update');
    Route::resource('social_link', SocialLinkController::class)->except(['show'])->parameters(['social_link' => 'socialLink']);
    Route::prefix('manajemen-data')->name('manajemen-data.')->group(function () {

    });
    Route::get('bungdes', [BungdesController::class, 'index'])->name('bungdes.index');
    Route::put('bungdes', [BungdesController::class, 'update'])->name('bungdes.update');
    Route::resource('unit_usaha', UnitUsahaController::class);
    Route::resource('akun', AkunController::class);
    Route::resource('user', UserController::class);
    Route::put('user/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('user.toggleActive');

});
