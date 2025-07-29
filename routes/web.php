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


// Keuangan
use App\Http\Controllers\Keuangan\KasBankController;
use App\Http\Controllers\Keuangan\TransaksiKasBankController;
use App\Http\Controllers\Keuangan\JurnalUmumController;

//laporan
use App\Http\Controllers\Laporan\BukuBesarController;
use App\Http\Controllers\Laporan\LabaRugiController;
use App\Http\Controllers\Laporan\NeracaController;

//usaha
use App\Http\Controllers\Usaha\ProdukController;
use App\Http\Controllers\Usaha\PenjualanController;
use App\Http\Controllers\Usaha\StokController;
use App\Http\Controllers\Usaha\PemasokController;
use App\Http\Controllers\Usaha\PembelianController;

use App\Http\Controllers\Admin\Aset\AsetBUMDesController;

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

    Route::prefix('manajemen-data')->name('manajemen-data.')->group (function () {
        Route::get('bungdes', [BungdesController::class, 'index'])->name('bungdes.index');
        Route::put('bungdes', [BungdesController::class, 'update'])->name('bungdes.update');
        Route::resource('unit_usaha', UnitUsahaController::class);
        Route::resource('akun', AkunController::class);
        Route::resource('user', UserController::class);
        Route::put('user/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('user.toggleActive');
    });
});

   // PENTING: Pindahkan rute spesifik ini di atas Route::resource
    Route::get('/bumdes/aset/penyusutan', [AsetBUMDesController::class, 'penyusutan'])->name('bumdes.aset.penyusutan');
    Route::get('/bumdes/aset/pemeliharaan', [AsetBUMDesController::class, 'pemeliharaan'])->name('bumdes.aset.pemeliharaan');

    Route::resource('bumdes/aset', AsetBUMDesController::class)->names([
        'index' => 'bumdes.aset.index',
        'create' => 'bumdes.aset.create',
        'store' => 'bumdes.aset.store',
        'show' => 'bumdes.aset.show',
        'edit' => 'bumdes.aset.edit',
        'update' => 'bumdes.aset.update',
        'destroy' => 'bumdes.aset.destroy',
    ]);

Route::prefix('keuangan')->group(function () {
    Route::resource('kas-bank', KasBankController::class);
    Route::post('transaksi-kas-bank', [TransaksiKasBankController::class, 'store'])->name('transaksi.store');
     Route::get('jurnal-umum', [JurnalUmumController::class, 'index'])->name('jurnal-umum.index');
});

Route::prefix('laporan')->name('laporan.')->group(function () {
    Route::get('buku-besar', [BukuBesarController::class, 'index'])->name('buku-besar.index');
    Route::post('buku-besar', [BukuBesarController::class, 'generate'])->name('buku-besar.generate');

    Route::get('laba-rugi', [LabaRugiController::class, 'index'])->name('laba-rugi.index');
    Route::post('laba-rugi', [LabaRugiController::class, 'generate'])->name('laba-rugi.generate');

    Route::get('neraca', [NeracaController::class, 'index'])->name('neraca.index');
    Route::post('neraca', [NeracaController::class, 'generate'])->name('neraca.generate');
});
Route::prefix('usaha')->name('usaha')->group(function () {
});
Route::resource('produk', ProdukController::class);
Route::resource('penjualan', PenjualanController::class);
Route::resource('pemasok', PemasokController::class);
<<<<<<< HEAD
Route::resource('pembelian', PembelianController::class);
=======
>>>>>>> cc8fb74dfe169ccfeae20d8d82f239253e9447cc
