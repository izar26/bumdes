<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function redirect()
    {
        $role = auth()->user()->role;

        if ($role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        // Tambahkan redirect lain jika kamu punya role lain
        return redirect('/'); // default
    }

    public function index()
    {
        return view('admin.dashboard');
    }
}
