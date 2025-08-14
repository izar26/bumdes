<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function redirect()
    {
        $role = auth()->user()->role;



        // Kalau role = admin_bumdes → ke dashboard admin
        if ($role === 'admin_bumdes') {
            return redirect()->route('home');
        }

        // Default ke dashboard admin (atau ubah sesuai kebutuhan)
        return redirect()->route('home');
    }

    public function index()
    {
        return view('admin.dashboard');
    }
}
