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

        // Optional logging from previous debugging steps, can be removed once fixed
        \Log::info('Attempting redirect for user: ' . ($user ? $user->username : 'NULL'));
        \Log::info('User role: ' . ($user ? $user->role : 'NULL'));

        if ($user && $user->role === 'admin') {
            \Log::info('Redirecting to admin dashboard.');
            return '/admin/dashboard';
        }

        \Log::info('Redirecting to default home.');
        return '/home'; // fallback
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
