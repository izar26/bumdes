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
use App\Http\Controllers\Admin\UserController;

use App\Http\Controllers\Admin\UnitUsahaController;
use App\Http\Controllers\Usaha\AdminUnitUsahaController;

// Keuangan
use App\Http\Controllers\Keuangan\JurnalUmumController;
use App\Http\Controllers\Keuangan\JurnalManualController;
use App\Http\Controllers\Keuangan\AkunController;
use App\Http\Controllers\Keuangan\ApprovalJurnalController;

// Laporan
use App\Http\Controllers\Laporan\BukuBesarController;
use App\Http\Controllers\Laporan\LabaRugiController;
use App\Http\Controllers\Laporan\NeracaController;
use App\Http\Controllers\Laporan\NeracaSaldoController;
use App\Http\Controllers\Laporan\PerubahanEkuitasController;

// Usaha
use App\Http\Controllers\Usaha\ProdukController;
use App\Http\Controllers\Usaha\PenjualanController;
use App\Http\Controllers\Usaha\StokController;
use App\Http\Controllers\Usaha\PemasokController;
use App\Http\Controllers\Usaha\PembelianController;

use App\Http\Controllers\Admin\Aset\AsetBUMDesController;
use App\Http\Controllers\Usaha\KategoriController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ProfileController;

Route::get('/', [HomeController::class, 'index']);

Auth::routes();

Route::middleware(['auth'])->group(function () {
    // Semua peran bisa mengakses dashboard dan profil pribadi
    Route::get('/admin/dashboard', fn () => view('admin.dashboard'))->name('admin.dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // =====================================================================================================================
    // ROUTE UNTUK ADMIN BUMDES
    // =====================================================================================================================
    Route::middleware(['role:admin_bumdes'])->group(function () {
        Route::prefix('admin/manajemen-data')->name('admin.manajemen-data.')->group(function () {
            Route::get('bungdes', [BungdesController::class, 'index'])->name('bungdes.index');
            Route::put('bungdes', [BungdesController::class, 'update'])->name('bungdes.update');
            Route::resource('user', UserController::class);
            Route::put('user/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('user.toggleActive');
            Route::resource('unit-usaha', UnitUsahaController::class)->except(['show'])->names('unit_usaha');
        });
        Route::prefix('admin')->name('admin.')->group(function () {
            Route::resource('berita', BeritaController::class);
            Route::resource('potensi', PotensiController::class);
            Route::get('profil', [ProfilController::class, 'edit'])->name('profil.edit'); // Profil Desa
            Route::put('profil', [ProfilController::class, 'update'])->name('profil.update'); // Profil Desa
            Route::get('pengaturan-halaman', [HomepageSettingController::class, 'edit'])->name('homepage_setting.edit');
            Route::put('pengaturan-halaman', [HomepageSettingController::class, 'update'])->name('homepage_setting.update');
            Route::resource('social_link', SocialLinkController::class)->except(['show'])->parameters(['social_link' => 'socialLink']);
        });
    });

    // =====================================================================================================================
    // ROUTE UNTUK KEPALA DESA
    // =====================================================================================================================
    Route::middleware(['role:kepala_desa'])->group(function () {
        Route::prefix('admin')->name('admin.')->group(function () {
            Route::resource('berita', BeritaController::class)->only(['index','show']);
            Route::resource('potensi', PotensiController::class)->only(['index','show']);
            Route::get('profil', [ProfilController::class, 'edit'])->name('profil.edit'); // Profil Desa
            Route::put('profil', [ProfilController::class, 'update'])->name('profil.update'); // Profil Desa
            Route::get('pengaturan-halaman', [HomepageSettingController::class, 'edit'])->name('homepage_setting.edit');
            Route::put('pengaturan-halaman', [HomepageSettingController::class, 'update'])->name('homepage_setting.update');
            Route::resource('social_link', SocialLinkController::class)->except(['show'])->parameters(['social_link' => 'socialLink']);
        });
    });

    // =====================================================================================================================
    // ROUTE UNTUK BENDHARA BUMDES
    // =====================================================================================================================
    Route::middleware(['role:bendahara_bumdes'])->group(function () {
        // Bendahara mengelola aset
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
        Route::prefix('keuangan')->name('keuangan.')->group(function () {
            Route::resource('akun', AkunController::class);
        });

    });

    // =====================================================================================================================
    // ROUTE UNTUK MANAJER UNIT USAHA
    // =====================================================================================================================
    Route::middleware(['role:manajer_unit_usaha'])->group(function () {
        Route::prefix('usaha')->name('usaha.')->group(function () {
            Route::resource('produk', ProdukController::class);
            Route::resource('stok', StokController::class);
            Route::resource('penjualan', PenjualanController::class);
            Route::resource('pembelian', PembelianController::class);
            Route::resource('pemasok', PemasokController::class);
            Route::resource('kategori', KategoriController::class)->except(['show']);
        });
    });

    Route::middleware(['role:admin_unit_usaha'])->group(function () {
        Route::prefix('usaha')->name('usaha.')->group(function () {
            Route::resource('produk', ProdukController::class);
            Route::resource('stok', StokController::class);
            Route::resource('penjualan', PenjualanController::class);
            Route::resource('pembelian', PembelianController::class);
            Route::resource('pemasok', PemasokController::class);
            Route::resource('kategori', KategoriController::class)->except(['show']);
            Route::get('unit-setting', [AdminUnitUsahaController::class, 'edit'])->name('unit_setting.edit');
            Route::put('unit-setting', [AdminUnitUsahaController::class, 'update'])->name('unit_setting.update');
            // Route::resource('unit_setting', AdminUnitUsahaController::class)->except(['index', 'show'])->names('unit_setting');

        });
    });

    // =====================================================================================================================
    // ROUTE KEUANGAN (Jurnal Umum & Manual)
    // Akses: admin_bumdes, bendahara_bumdes, admin_unit_usaha
    // =====================================================================================================================
    Route::middleware(['role:admin_bumdes|bendahara_bumdes|admin_unit_usaha'])->group(function () {
        Route::prefix('keuangan')->group(function () {
            Route::get('jurnal-manual/create', [JurnalManualController::class, 'create'])->name('jurnal-manual.create');
            Route::post('jurnal-manual', [JurnalManualController::class, 'store'])->name('jurnal-manual.store');
            Route::resource('jurnal-umum', JurnalUmumController::class);

        });
    });

    // =====================================================================================================================
    // ROUTE LAPORAN
    // Akses: admin_bumdes, bendahara_bumdes, kepala_desa, admin_unit_usaha, manajer_unit_usaha
    // =====================================================================================================================
    Route::middleware(['role:bendahara_bumdes'])->group(function () {
        Route::prefix('laporan')->name('laporan.')->group(function () {
            Route::get('buku-besar', [BukuBesarController::class, 'index'])->name('buku-besar.index');
            Route::post('buku-besar', [BukuBesarController::class, 'generate'])->name('buku-besar.generate');
            Route::get('laba-rugi', [LabaRugiController::class, 'index'])->name('laba-rugi.index');
            Route::post('laba-rugi', [LabaRugiController::class, 'generate'])->name('laba-rugi.generate');
            Route::get('neraca', [NeracaController::class, 'index'])->name('neraca.index');
            Route::post('neraca', [NeracaController::class, 'generate'])->name('neraca.generate');
            Route::get('neraca-saldo', [NeracaSaldoController::class, 'index'])->name('neraca-saldo.index');
            Route::post('neraca-saldo', [NeracaSaldoController::class, 'generate'])->name('neraca-saldo.generate');
            Route::get('perubahan-ekuitas', [PerubahanEkuitasController::class, 'index'])->name('perubahan-ekuitas.index');
            Route::post('perubahan-ekuitas', [PerubahanEkuitasController::class, 'generate'])->name('perubahan-ekuitas.generate');
        });
    });

    // Rute Buku Besar untuk Manajer Unit Usaha di luar grup laporan
    Route::middleware(['role:manajer_unit_usaha'])->group(function () {
        Route::get('buku-besar', [BukuBesarController::class, 'index'])->name('buku-besar.index');
        Route::post('buku-besar', [BukuBesarController::class, 'generate'])->name('buku-besar.generate');
    });
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout');

Route::get('/admin/dashboard', fn () => view('admin.dashboard'))
    ->name('home')
    ->middleware('auth');
