<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Gates untuk peran tunggal
        Gate::define('admin_bumdes', fn($user) => $user->role === 'admin_bumdes');
        Gate::define('kepala_desa', fn($user) => $user->role === 'kepala_desa');
        Gate::define('bendahara_bumdes', fn($user) => $user->role === 'bendahara_bumdes');
        Gate::define('manajer_unit_usaha', fn($user) => $user->role === 'manajer_unit_usaha');
        Gate::define('sekretaris_bumdes', fn($user) => $user->role === 'sekretaris_bumdes');
        Gate::define('admin_unit_usaha', fn($user) => $user->role === 'admin_unit_usaha');
        Gate::define('anggota_baru', fn($user) => $user->role === 'anggota_baru');


        // Gates untuk kombinasi peran
        Gate::define('admin_bumdes_or_kepala_desa', fn($user) =>
            in_array($user->role, ['admin_bumdes', 'kepala_desa'])
        );
        Gate::define('bendahara_bumdes_or_kepala_desa', fn($user) =>
            in_array($user->role, ['bendahara_bumdes', 'kepala_desa'])
        );
        Gate::define('manajer_unit_usaha_or_admin_unit_usaha', fn($user) =>
            in_array($user->role, ['manajer_unit_usaha', 'admin_unit_usaha'])
        );
        Gate::define('bendahara_bumdes_or_manajer_unit_usaha_or_admin_unit_usaha', fn($user) =>
            in_array($user->role, ['bendahara_bumdes', 'manajer_unit_usaha', 'admin_unit_usaha'])
        );
        Gate::define('direktur_or_sekretaris_bumdes', fn($user) =>
            in_array($user->role, ['admin_bumdes', 'sekretaris_bumdes'])
        );
    }
}
