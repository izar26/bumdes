<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\BeritaController;
use App\Http\Controllers\Admin\PotensiController;
use App\Http\Controllers\Admin\ProfilController;
use App\Http\Controllers\Admin\HomepageSettingController;
use App\Http\Controllers\Admin\SocialLinkController;
use App\Http\Controllers\HomeController;


Route::get('/', [HomeController::class, 'index']);

Auth::routes();
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Route untuk dashboard
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
});
