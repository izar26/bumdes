<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Bungdes; // 1. Tambahkan ini untuk memanggil model Bungdes

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

        // 2. Perbaikan: Mengambil nama peran menggunakan metode dari Spatie
        $role = $user->getRoleNames()->first();

        \Log::info('User attempting redirect: ' . $user->username);
        \Log::info('User role: ' . $role);

        switch ($role) {
            case 'kepala_desa':
            case 'sekretaris_bumdes':
            case 'bendahara_bumdes':
            case 'admin_bumdes':
                return '/dashboard';
            case 'anggota':
                return '/profile';
            case 'manajer_unit_usaha':
            case 'admin_unit_usaha':
                return '/dashboard';
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
     * 3. Tambahkan method ini untuk mengirim data ke halaman login
     * Show the application's login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        $bumdes = Bungdes::first();
        // Pastikan path view sesuai dengan lokasi file login Anda
        return view('auth.login', compact('bumdes'));
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
