<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\PendidikController;
use App\Http\Controllers\Admin\TapelApiController;
use App\Http\Controllers\Admin\DokumenController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Anggota\AnggotaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\Aset\AsetBUMDesController;
use App\Http\Controllers\Admin\BeritaController;
use App\Http\Controllers\Admin\BungdesController;
use App\Http\Controllers\Admin\HomepageSettingController;
use App\Http\Controllers\Admin\PotensiController;
use App\Http\Controllers\Admin\ProfilController as AdminProfilController;
use App\Http\Controllers\Admin\SocialLinkController;
use App\Http\Controllers\Admin\UnitUsahaController;
use App\Http\Controllers\Usaha\AdminUnitUsahaController;
use App\Http\Controllers\Usaha\KategoriController;
use App\Http\Controllers\Usaha\PembelianController;
use App\Http\Controllers\Usaha\PenjualanController;
use App\Http\Controllers\Usaha\PemasokController;
use App\Http\Controllers\Usaha\ProdukController;
use App\Http\Controllers\Usaha\StokController;
use App\Http\Controllers\Keuangan\AkunController;
use App\Http\Controllers\Keuangan\ApprovalJurnalController;
use App\Http\Controllers\Keuangan\JurnalManualController;
use App\Http\Controllers\Keuangan\JurnalUmumController;
use App\Http\Controllers\Keuangan\KasBankController;
use App\Http\Controllers\Keuangan\TransaksiKasBankController;
use App\Http\Controllers\Laporan\BukuBesarController;
use App\Http\Controllers\Laporan\LabaRugiController;
use App\Http\Controllers\Laporan\NeracaController;
use App\Http\Controllers\Laporan\NeracaSaldoController;
use App\Http\Controllers\Laporan\PerubahanEkuitasController;
use App\Http\Controllers\Laporan\ArusKasController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Halaman utama
Route::get('/', [HomeController::class, 'index'])->name('home');

// Auth routes
Auth::routes();

// Logout
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout');

// Semua route setelah login (tanpa paksaan melengkapi profil)
Route::middleware(['auth'])->group(function () {

    // Redirect ke dashboard sesuai role
    Route::get('/dashboard', [DashboardController::class, 'redirect'])->name('dashboard.redirect');

    // Halaman Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/account', [ProfileController::class, 'updateAccount'])->name('profile.update-account');
    Route::post('/profile/personal', [ProfileController::class, 'updatePersonal'])->name('profile.update-personal');

    // Dashboard Admin

    /*
    |--------------------------------------------------------------------------
    | ADMIN BUMDes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin_bumdes'])->prefix('admin')->name('admin.')->group(function () {
        Route::prefix('manajemen-data')->name('manajemen-data.')->group(function () {
            Route::get('bungdes', [BungdesController::class, 'index'])->name('bungdes.index');
            Route::put('bungdes', [BungdesController::class, 'update'])->name('bungdes.update');
            Route::resource('user', UserController::class)->names('user');
            Route::resource('manajemen-data/user', UserController::class)->names('admin.manajemen-data.user');
            Route::put('user/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('user.toggleActive');
            Route::resource('unit-usaha', UnitUsahaController::class)->except(['show'])->names('unit_usaha');
            Route::resource('anggota', AnggotaController::class)    ->names('anggota');
            Route::put('manajemen-data/anggota/{anggota}', [AnggotaController::class, 'update'])->name('admin.manajemen-data.anggota.update');
            Route::put('manajemen-data/anggota/{user}/update-role', [AnggotaController::class, 'updateRole'])->name('anggota.updateRole');
        });

        Route::resource('berita', BeritaController::class)->names('berita');
        Route::resource('potensi', PotensiController::class)->names('potensi');
        Route::get('profil', [AdminProfilController::class, 'edit'])->name('profil.edit');
        Route::put('profil', [AdminProfilController::class, 'update'])->name('profil.update');
        Route::get('pengaturan-halaman', [HomepageSettingController::class, 'edit'])->name('homepage_setting.edit');
        Route::put('pengaturan-halaman', [HomepageSettingController::class, 'update'])->name('homepage_setting.update');
        Route::resource('social_link', SocialLinkController::class)->except(['show'])->names('social_link');
        // Route::resource('akun', AkunController::class);

    });

    /*
    |--------------------------------------------------------------------------
    | BENDAHARA & SEKRETARIS
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:bendahara_bumdes|sekretaris_bumdes'])->group(function () {
        Route::prefix('bumdes/aset')->name('bumdes.')->group(function () {
            Route::resource('aset', AsetBUMDesController::class);
            Route::get('penyusutan', [AsetBUMDesController::class, 'penyusutan'])->name('aset.penyusutan');
            Route::get('pemeliharaan', [AsetBUMDesController::class, 'pemeliharaan'])->name('aset.pemeliharaan');
        });

        Route::prefix('keuangan')->name('keuangan.')->group(function () {
            Route::resource('akun', AkunController::class);
            Route::resource('kas-bank', KasBankController::class)->names('kas-bank');
            Route::post('kas-bank/{kasBank}/transaksi', [TransaksiKasBankController::class, 'store'])->name('kas-bank.transaksi.store');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | UNIT USAHA
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:manajer_unit_usaha|admin_unit_usaha'])->prefix('usaha')->name('usaha.')->group(function () {
        Route::resource('produk', ProdukController::class)->names('produk');
        Route::resource('stok', StokController::class)->names('stok');
        Route::resource('penjualan', PenjualanController::class)->names('penjualan');
        Route::resource('pembelian', PembelianController::class)->names('pembelian');
        Route::resource('pemasok', PemasokController::class)->names('pemasok');
        Route::resource('kategori', KategoriController::class)->except(['show'])->names('kategori');

        Route::middleware(['role:admin_unit_usaha'])->group(function () {
            Route::get('unit-setting', [AdminUnitUsahaController::class, 'edit'])->name('unit_setting.edit');
            Route::put('unit-setting', [AdminUnitUsahaController::class, 'update'])->name('unit_setting.update');
        });
    });

    // =====================================================================================================================
    // ROUTE KEUANGAN (Jurnal Umum & Manual)
    // Akses: admin_bumdes, bendahara_bumdes, admin_unit_usaha
    // =====================================================================================================================
    Route::middleware(['role:bendahara_bumdes|admin_unit_usaha|sekretaris_bumdes'])->group(function () {
        Route::prefix('keuangan')->group(function () {
            Route::get('jurnal-manual/create', [JurnalManualController::class, 'create'])->name('jurnal-manual.create');
            Route::post('jurnal-manual', [JurnalManualController::class, 'store'])->name('jurnal-manual.store');
            Route::resource('jurnal-umum', JurnalUmumController::class);
        });
    });




    Route::middleware(['role:admin_unit_usaha|bendahara_bumdes'])->group(function () {
        Route::prefix('keuangan')->group(function () {
            Route::get('jurnal-manual/create', [JurnalManualController::class, 'create'])->name('jurnal-manual.create');
            Route::post('jurnal-manual', [JurnalManualController::class, 'store'])->name('jurnal-manual.store');
            Route::resource('jurnal-umum', JurnalUmumController::class);

        });

    });
    Route::middleware(['role:manajer_unit_usaha|direktur_bumdes'])->group(function () {
            Route::get('keuangan/approval-jurnal', [ApprovalJurnalController::class, 'index'])->name('approval-jurnal.index');
            Route::post('keuangan/approval-jurnal/{jurnal}/approve', [ApprovalJurnalController::class, 'approve'])->name('approval-jurnal.approve');
            Route::post('keuangan/approval-jurnal/{jurnal}/reject', [ApprovalJurnalController::class, 'reject'])->name('approval-jurnal.reject');

             Route::prefix('laporan')->name('laporan.')->group(function () {
            Route::get('buku-besar', [BukuBesarController::class, 'index'])->name('buku-besar.index');
            Route::post('buku-besar', [BukuBesarController::class, 'generate'])->name('buku-besar.generate');
            Route::get('laba-rugi', [LabaRugiController::class, 'index'])->name('laba-rugi.index');
            Route::post('laba-rugi', [LabaRugiController::class, 'generate'])->name('laba-rugi.generate');
        });
    });


    // =====================================================================================================================
    // ROUTE LAPORAN
    // Akses: admin_bumdes, bendahara_bumdes, kepala_desa, admin_unit_usaha, manajer_unit_usaha
    // =====================================================================================================================
    Route::middleware(['role:bendahara_bumdes|sekretaris_bumdes'])->group(function () {
        Route::prefix('laporan')->name('laporan.')->group(function () {
            Route::get('laba-rugi', [LabaRugiController::class, 'index'])->name('laba-rugi.index');
            Route::post('laba-rugi', [LabaRugiController::class, 'generate'])->name('laba-rugi.generate');
            Route::get('neraca', [NeracaController::class, 'index'])->name('neraca.index');
            Route::post('neraca', [NeracaController::class, 'generate'])->name('neraca.generate');
            Route::get('neraca-saldo', [NeracaSaldoController::class, 'index'])->name('neraca-saldo.index');
            Route::post('neraca-saldo', [NeracaSaldoController::class, 'generate'])->name('neraca-saldo.generate');
            Route::get('perubahan-ekuitas', [PerubahanEkuitasController::class, 'index'])->name('perubahan-ekuitas.index');
            Route::post('perubahan-ekuitas', [PerubahanEkuitasController::class, 'generate'])->name('perubahan-ekuitas.generate');

            Route::get('arus-kas', [ArusKasController::class, 'index'])->name('arus-kas.index');
            Route::post('arus-kas', [ArusKasController::class, 'generate'])->name('arus-kas.generate');
        });
    });

    // Rute Buku Besar untuk Manajer Unit Usaha di luar grup laporan
    });

     Route::prefix('laporan')->name('laporan.')->group(function () {
            Route::get('buku-besar', [BukuBesarController::class, 'index'])->name('buku-besar.index');
            Route::post('buku-besar', [BukuBesarController::class, 'generate'])->name('buku-besar.generate');
            Route::get('laba-rugi', [LabaRugiController::class, 'index'])->name('laba-rugi.index');
            Route::post('laba-rugi', [LabaRugiController::class, 'generate'])->name('laba-rugi.generate');
        });


    Route::get('laporan/arus-kas', [ArusKasController::class, 'index'])->name('arus-kas.index');
    Route::post('laporan/arus-kas', [ArusKasController::class, 'generate'])->name('arus-kas.generate');

Route::get('/admin/dashboard', fn () => view('admin.dashboard'))
    ->name('home')
    ->middleware('auth');
