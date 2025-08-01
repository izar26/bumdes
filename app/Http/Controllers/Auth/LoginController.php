<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Models\User; // Make sure this is imported

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * The primary key for the user table.
     * This tells AuthenticatesUsers trait to use 'user_id' instead of 'id'.
     */
    protected $primaryKey = 'user_id'; // <--- ADD THIS LINE

    /**
     * Where to redirect users after login.
     *
     * @var string
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
        case 'admin_bumdes':
            return '/admin/dashboard';
        case 'bendahara_bumdes':
            return '/admin/dashboard';
        case 'manajer_unit_usaha':
            return '/anggot/dashboard';
        case 'kepala_desa':
            return '/admin/dashboard';
        case 'bendahara_bumdes':
            return '/admin/dashboard';
        default:
            return '/home';
    }
}


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Memberitahu Laravel untuk menggunakan 'username' untuk login,
     * bukan 'email'.
     */
    public function username()
    {
        return 'username';
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        // Update the last_login timestamp
        $user->last_login = now();
        $user->save(); // This is the line that caused the error before! Now it should work.

        // The default redirect will then proceed via redirectTo()
        // return redirect()->intended($this->redirectPath()); // Uncomment if you want to override redirectTo logic
    }
}
