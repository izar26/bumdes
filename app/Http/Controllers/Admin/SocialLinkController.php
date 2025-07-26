<?php
// app/Http/Controllers/Admin/SocialLinkController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialLink;
use Illuminate\Http\Request;

class SocialLinkController extends Controller
{
    public function index(Request $request)
    {
        $links = SocialLink::latest()->get();
        $link_edit = new SocialLink();
        if ($request->filled('edit')) {
            $link_edit = SocialLink::find($request->edit);
        }
        return view('admin.social_link.index', compact('links', 'link_edit'));
    }

    public function store(Request $request)
    {
        $request->validate(['platform' => 'required', 'icon' => 'required', 'url' => 'required|url']);
        SocialLink::create($request->all());
        return redirect()->route('admin.social_link.index')->with('success', 'Link berhasil ditambahkan.');
    }

    public function update(Request $request, SocialLink $socialLink)
    {
        $request->validate(['platform' => 'required', 'icon' => 'required', 'url' => 'required|url']);
        $socialLink->update($request->all());
        return redirect()->route('admin.social_link.index')->with('success', 'Link berhasil diperbarui.');
    }

    public function destroy(SocialLink $socialLink)
    {
        $socialLink->delete();
        return redirect()->route('admin.social_link.index')->with('success', 'Link berhasil dihapus.');
    }
}
