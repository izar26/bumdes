<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Models\User;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * The primary key for the user table.
     * This tells AuthenticatesUsers trait to use 'user_id' instead of 'id'.
     */
    protected $primaryKey = 'user_id';

    /**
     * Where to redirect users after login.
     */
    protected function redirectTo()
    {
        $user = auth()->user();

        if (!$user) {
            \Log::warning('Redirect attempted without authenticated user.');
            return '/login';
        }

        \Log::info('User attempting redirect: ' . $user->username);
        \Log::info('User role: ' . $user->role);

        switch ($user->role) {
            case 'kepala_desa':
                return '/admin/dashboard';
            case 'manajer_unit_usaha':
                return '/admin/dashboard';
            case 'admin_unit_usaha':
                return '/admin/dashboard';
            case 'sekretaris_bumdes':
                return '/admin/dashboard';
            case 'bendahara_bumdes':
                return '/admin/dashboard';
            case 'admin_bumdes':
                return '/admin/dashboard';
            case 'anggota_baru':
                return '/profile';
            case 'anggota':
                return '/profile';
            default:
                return '/login';
        }
    }

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Use 'username' instead of 'email' for login.
     */
    public function username()
    {
        return 'username';
    }

    /**
     * Override the credentials used in login to include `is_active = 1`.
     */
    protected function credentials(Request $request)
    {
        return [
            'username'  => $request->get('username'),
            'password'  => $request->get('password'),
            'is_active' => 1,
        ];
    }

    /**
     * After user is authenticated, update their last_login time.
     */
    protected function authenticated(Request $request, $user)
    {
        $user->last_login = now();
        $user->save();
    }
}
