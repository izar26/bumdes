<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UnitUsaha;
use App\Models\Bungdes;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UnitUsahaController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = UnitUsaha::with(['bungdes', 'user']);

        if ($user->isManajerUnitUsaha()) {
            $query->where('user_id', $user->user_id);
        }

        $unitUsahas = $query->latest()->get();
        return view('admin.manajemen_data.unit_usaha.index', compact('unitUsahas'));
    }

    /**
     * Menampilkan form untuk menambah unit usaha baru.
     */
    public function create()
    {
        // Hanya admin_bumdes yang boleh mengakses form ini
        if (!Auth::user()->isAdminBumdes()) {
            abort(403, 'Anda tidak memiliki akses untuk membuat Unit Usaha.');
        }

        $bungdeses = Bungdes::all();
        // Hanya tampilkan user dengan role manajer_unit_usaha untuk pilihan penanggung jawab
        $users = User::where('role', 'manajer_unit_usaha')->get();
        return view('admin.manajemen_data.unit_usaha.create', compact('bungdeses', 'users'));
    }

    /**
     * Menyimpan unit usaha baru ke database.
     */
    public function store(Request $request)
    {
        // Hanya admin_bumdes yang boleh melakukan operasi ini
        if (!Auth::user()->isAdminBumdes()) {
            abort(403, 'Anda tidak memiliki akses untuk menyimpan Unit Usaha.');
        }

        $rules = [
            'nama_unit' => 'required|string|max:255|unique:unit_usahas,nama_unit', // Tambahkan unique
            'jenis_usaha' => 'required|string|max:100',
            'bungdes_id' => 'required|exists:bungdeses,bungdes_id',
            'tanggal_mulai_operasi' => 'nullable|date',
            'status_operasi' => ['required', 'string', Rule::in(['Aktif', 'Tidak Aktif', 'Dalam Pengembangan'])], // Contoh: batasi pilihan
            'user_id' => 'nullable|exists:users,user_id', // Admin bisa menugaskan
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->only([
            'nama_unit',
            'jenis_usaha',
            'bungdes_id',
            'tanggal_mulai_operasi',
            'status_operasi',
            'user_id' // Admin BUMDes bisa memilih user_id
        ]);

        // Pastikan user_id yang dipilih memang manajer_unit_usaha, jika ada
        if ($request->filled('user_id')) {
            $selectedUser = User::find($request->user_id);
            if (!$selectedUser || !$selectedUser->isManajerUnitUsaha()) {
                return redirect()->back()->withErrors(['user_id' => 'Penanggung Jawab harus memiliki peran Manajer Unit Usaha.'])->withInput();
            }
        } else {
            // Jika user_id tidak dipilih, pastikan value-nya null
            $data['user_id'] = null;
        }

        UnitUsaha::create($data);

        return redirect()->route('admin.manajemen-data.unit_usaha.index')->with('success', 'Unit usaha berhasil ditambahkan!');
    }

    public function show(UnitUsaha $unitUsaha)
    {
        $unitUsaha->load(['bungdes', 'user']);
        return view('admin.manajemen_data.unit_usaha.show', compact('unitUsaha'));
    }

    /**
     * Menampilkan form untuk mengedit unit usaha.
     */
    public function edit(UnitUsaha $unitUsaha)
    {
        $user = Auth::user();

        // Otorisasi: Hanya admin_bumdes yang bisa mengedit unit usaha mana pun.
        // Manajer unit usaha hanya bisa mengedit unit usaha yang dia kelola.
        if ($user->isManajerUnitUsaha() && $unitUsaha->user_id !== $user->user_id) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit unit usaha ini.');
        }

        $bungdeses = Bungdes::all();
        $users = User::where('role', 'manajer_unit_usaha')->get();
        // Path view diperbaiki
        return view('admin.manajemen_data.unit_usaha.edit', compact('unitUsaha', 'bungdeses', 'users'));
    }

    /**
     * Memperbarui unit usaha di database.
     */
    public function update(Request $request, UnitUsaha $unitUsaha)
    {
        $loggedInUser = Auth::user();

        // Otorisasi sama seperti di edit
        if ($loggedInUser->isManajerUnitUsaha() && $unitUsaha->user_id !== $loggedInUser->user_id) {
            abort(403, 'Anda tidak memiliki akses untuk memperbarui unit usaha ini.');
        }

        $rules = [
            'nama_unit' => ['required', 'string', 'max:255', Rule::unique('unit_usahas', 'nama_unit')->ignore($unitUsaha->unit_usaha_id, 'unit_usaha_id')], // Unique, kecuali dirinya sendiri
            'jenis_usaha' => 'required|string|max:100',
            'bungdes_id' => 'required|exists:bungdeses,bungdes_id',
            'tanggal_mulai_operasi' => 'nullable|date',
            'status_operasi' => ['required', 'string', Rule::in(['Aktif', 'Tidak Aktif', 'Dalam Pengembangan'])],
        ];

        // Hanya admin_bumdes yang bisa mengubah user_id (penanggung jawab)
        if ($loggedInUser->isAdminBumdes()) {
            $rules['user_id'] = 'nullable|exists:users,user_id';
        } else {
            // Jika bukan admin_bumdes, user_id tidak boleh diubah
            // Ini akan memastikan jika manajer mengedit, dia tidak bisa ganti penanggung jawab
            $rules['user_id'] = ['nullable', Rule::in([$unitUsaha->user_id])]; // user_id hanya boleh nilai yang sama atau null
        }


        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->only([
            'nama_unit',
            'jenis_usaha',
            'bungdes_id',
            'tanggal_mulai_operasi',
            'status_operasi',
        ]);

        // Hanya tambahkan user_id ke $data jika admin_bumdes yang mengedit atau jika user_id dikirimkan dan valid
        if ($loggedInUser->isAdminBumdes()) {
             // Pastikan user_id yang dipilih memang manajer_unit_usaha, jika ada
            if ($request->filled('user_id')) {
                $selectedUser = User::find($request->user_id);
                if (!$selectedUser || !$selectedUser->isManajerUnitUsaha()) {
                    return redirect()->back()->withErrors(['user_id' => 'Penanggung Jawab harus memiliki peran Manajer Unit Usaha.'])->withInput();
                }
                $data['user_id'] = $request->user_id;
            } else {
                $data['user_id'] = null; // Jika admin ingin mengosongkan penanggung jawab
            }
        } else {
            // Jika bukan admin_bumdes, user_id tidak boleh diubah, biarkan seperti semula
            $data['user_id'] = $unitUsaha->user_id;
        }


        $unitUsaha->update($data);

        return redirect()->route('admin.manajemen-data.unit_usaha.index')->with('success', 'Unit usaha berhasil diperbarui!');
    }

    /**
     * Menghapus unit usaha dari database.
     */
    public function destroy(UnitUsaha $unitUsaha)
    {
        // Hanya admin_bumdes yang boleh menghapus unit usaha
        if (!Auth::user()->isAdminBumdes()) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus Unit Usaha.');
        }

        // TODO: Pertimbangkan validasi jika unit usaha ini memiliki data terkait (produk, transaksi)
        // yang harus dihapus atau dipindahkan sebelum unit usaha dihapus.

        $unitUsaha->delete();
        return redirect()->route('admin.manajemen-data.unit_usaha.index')->with('success', 'Unit usaha berhasil dihapus!');
    }
}
