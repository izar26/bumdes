<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Profil;
use App\Models\HomepageSetting;
use App\Models\Potensi;
use App\Models\Berita;
use App\Models\SocialLink;

class PageController extends Controller
{
    public function index()
    {
        // Ambil data dari semua model
        $profil = Profil::firstOrFail();
        $settings = HomepageSetting::firstOrFail();
        $potensis = Potensi::latest()->take(3)->get();
        $beritas = Berita::latest()->take(3)->get();
        $socials = SocialLink::all();
        
        // Kirim semua data ke view 'welcome'
        return view('welcome', [
            'profil' => $profil,
            'settings' => $settings,
            'potensis' => $potensis,
            'beritas' => $beritas,
            'socials' => $socials,
        ]);
    }
}