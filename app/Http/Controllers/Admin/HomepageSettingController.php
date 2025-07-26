<?php
// app/Http/Controllers/Admin/HomepageSettingController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomepageSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomepageSettingController extends Controller
{
    public function edit()
    {
        $settings = HomepageSetting::firstOrFail();
        return view('admin.homepage_setting.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $settings = HomepageSetting::firstOrFail();
        $request->validate([
            'hero_headline' => 'required|string',
            'hero_tagline' => 'required|string',
            'hero_background' => 'nullable|image|max:2048',
            'profil_image' => 'nullable|image|max:2048',
        ]);

        $data = $request->except(['hero_background', 'profil_image']);

        // Proses upload background hero
        if ($request->hasFile('hero_background')) {
            if ($settings->hero_background) Storage::disk('public')->delete($settings->hero_background);
            $data['hero_background'] = $request->file('hero_background')->store('homepage', 'public');
        }

        // Proses upload gambar profil
        if ($request->hasFile('profil_image')) {
            if ($settings->profil_image) Storage::disk('public')->delete($settings->profil_image);
            $data['profil_image'] = $request->file('profil_image')->store('homepage', 'public');
        }

        $settings->update($data);

        return redirect()->route('admin.homepage_setting.edit')->with('success', 'Pengaturan Halaman berhasil diperbarui.');
    }
}