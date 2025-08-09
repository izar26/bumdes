<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UnitUsaha;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UnitUsahaController extends Controller
{
    /**
     * Menampilkan daftar semua unit usaha.
     * Manajer Unit Usaha hanya bisa melihat unit yang dia kelola.
     * Admin BUMDes melihat semua unit usaha.
     */
    public function index()
    {
        $user = Auth::user();
        $query = UnitUsaha::with(['users']);

        if ($user->hasAnyRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
            // Menggunakan whereHas untuk memastikan relasi users tidak kosong
            $unitUsahas = $query->whereHas('users', function ($q) use ($user) {
                $q->where('user_id', $user->user_id);
            })->latest()->get();
        } else {
            $unitUsahas = $query->latest()->get();
        }

        return view('admin.manajemen_data.unit_usaha.index', compact('unitUsahas'));
    }

    /**
     * Menampilkan form untuk menambah unit usaha baru.
     */
    public function create()
    {
        // Ambil user yang berhak menjadi penanggung jawab dan belum terhubung ke unit usaha manapun
        $users = User::role(['manajer_unit_usaha', 'admin_unit_usaha'])
                    ->whereDoesntHave('unitUsahas')
                    ->orderBy('name')
                    ->get();
        return view('admin.manajemen_data.unit_usaha.create', compact('users'));
    }

    /**
     * Menyimpan unit usaha baru ke database.
     */
    public function store(Request $request)
    {
        $rules = [
            'nama_unit' => 'required|string|max:255|unique:unit_usahas,nama_unit',
            'jenis_usaha' => 'required|string|max:100',
            'tanggal_mulai_operasi' => 'nullable|date',
            'status_operasi' => ['required', 'string', Rule::in(['Aktif', 'Tidak Aktif', 'Dalam Pengembangan'])],
            'penanggung_jawab_ids' => 'nullable|array',
            'penanggung_jawab_ids.*' => [
                'exists:users,user_id',
                Rule::unique('unit_usaha_user', 'user_id'),
                Rule::in(User::role(['manajer_unit_usaha', 'admin_unit_usaha'])->pluck('user_id')),
            ],
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $unitUsaha = UnitUsaha::create($request->only([
                'nama_unit',
                'jenis_usaha',
                'tanggal_mulai_operasi',
                'status_operasi'
            ]));
            $unitUsaha->users()->sync($request->input('penanggung_jawab_ids', []));

            DB::commit();
            return redirect()->route('admin.manajemen-data.unit_usaha.index')->with('success', 'Unit usaha berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menambahkan unit usaha: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Menampilkan form untuk mengedit unit usaha.
     */

public function edit(UnitUsaha $unitUsaha)
{
    $user = Auth::user();

    if (!$user->hasRole('admin_bumdes') && !$unitUsaha->users->contains($user->user_id)) {
        abort(403, 'Anda tidak memiliki akses untuk mengedit unit usaha ini.');
    }

    // Ambil user yang berhak menjadi penanggung jawab.
    // FIX: Menambahkan nama tabel 'unit_usaha_user' di depan kolom 'unit_usaha_id'
    $users = User::role(['manajer_unit_usaha', 'admin_unit_usaha'])
                ->where(function($query) use ($unitUsaha) {
                    $query->whereDoesntHave('unitUsahas')
                          ->orWhereHas('unitUsahas', function($query) use ($unitUsaha) {
                              $query->where('unit_usaha_user.unit_usaha_id', $unitUsaha->unit_usaha_id);
                          });
                })
                ->orderBy('name')
                ->get();

    $assignedUserIds = $unitUsaha->users->pluck('user_id')->toArray();
    return view('admin.manajemen_data.unit_usaha.edit', compact('unitUsaha', 'users', 'assignedUserIds'));
}

    /**
     * Memperbarui unit usaha di database.
     */
    public function update(Request $request, UnitUsaha $unitUsaha)
    {
        $loggedInUser = Auth::user();

        if (!$loggedInUser->hasRole('admin_bumdes') && !$unitUsaha->users->contains($loggedInUser->user_id)) {
            abort(403, 'Anda tidak memiliki akses untuk memperbarui unit usaha ini.');
        }

        $rules = [
            'nama_unit' => ['required', 'string', 'max:255', Rule::unique('unit_usahas', 'nama_unit')->ignore($unitUsaha->unit_usaha_id, 'unit_usaha_id')],
            'jenis_usaha' => 'required|string|max:100',
            'tanggal_mulai_operasi' => 'nullable|date',
            'status_operasi' => ['required', 'string', Rule::in(['Aktif', 'Tidak Aktif', 'Dalam Pengembangan'])],
            'penanggung_jawab_ids' => 'nullable|array',
            'penanggung_jawab_ids.*' => [
                'exists:users,user_id',
                Rule::unique('unit_usaha_user', 'user_id')->ignore($unitUsaha->unit_usaha_id, 'unit_usaha_id'),
                Rule::in(User::role(['manajer_unit_usaha', 'admin_unit_usaha'])->pluck('user_id')),
            ],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $unitUsaha->update($request->only([
                'nama_unit',
                'jenis_usaha',
                'tanggal_mulai_operasi',
                'status_operasi',
            ]));

            if ($loggedInUser->hasRole('admin_bumdes')) {
                 $unitUsaha->users()->sync($request->input('penanggung_jawab_ids', []));
            }

            DB::commit();
            return redirect()->route('admin.manajemen-data.unit_usaha.index')->with('success', 'Unit usaha berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui unit usaha: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(UnitUsaha $unitUsaha)
    {
        // Hanya Admin BUMDes yang bisa menghapus unit usaha
        if (!Auth::user()->hasRole('admin_bumdes')) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus Unit Usaha.');
        }

        DB::beginTransaction();
        try {
            $unitUsaha->delete();
            DB::commit();
            return redirect()->route('admin.manajemen-data.unit_usaha.index')->with('success', 'Unit usaha berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus unit usaha: ' . $e->getMessage());
        }
    }
}
