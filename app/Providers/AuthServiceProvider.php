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

        // Gates untuk peran tunggal menggunakan Spatie
        Gate::define('admin_bumdes', fn($user) => $user->hasRole('admin_bumdes'));
        Gate::define('kepala_desa', fn($user) => $user->hasRole('kepala_desa'));
        Gate::define('bendahara_bumdes', fn($user) => $user->hasRole('bendahara_bumdes'));
        Gate::define('manajer_unit_usaha', fn($user) => $user->hasRole('manajer_unit_usaha'));
        Gate::define('sekretaris_bumdes', fn($user) => $user->hasRole('sekretaris_bumdes'));
        Gate::define('admin_unit_usaha', fn($user) => $user->hasRole('admin_unit_usaha'));
        Gate::define('anggota', fn($user) => $user->hasRole('anggota'));

        // Gates untuk kombinasi peran menggunakan Spatie
        Gate::define('admin_bumdes_or_kepala_desa', fn($user) =>
            $user->hasAnyRole(['admin_bumdes', 'kepala_desa'])
        );
        Gate::define('bendahara_bumdes_or_kepala_desa', fn($user) =>
            $user->hasAnyRole(['bendahara_bumdes', 'kepala_desa'])
        );
        Gate::define('manajer_unit_usaha_or_admin_unit_usaha', fn($user) =>
            $user->hasAnyRole(['manajer_unit_usaha', 'admin_unit_usaha'])
        );
        Gate::define('bendahara_bumdes_or_manajer_unit_usaha_or_admin_unit_usaha', fn($user) =>
            $user->hasAnyRole(['bendahara_bumdes', 'manajer_unit_usaha', 'admin_unit_usaha'])
        );
        Gate::define('direktur_or_sekretaris_bumdes_or_bendahara_bumdes', fn($user) =>
            $user->hasAnyRole(['direktur_bumdes', 'sekretaris_bumdes', 'bendahara_bumdes'])
        );
        Gate::define('direktur_or_sekretaris_bumdes', fn($user) =>
            $user->hasAnyRole(['admin_bumdes', 'sekretaris_bumdes'])
        );
    }
}
