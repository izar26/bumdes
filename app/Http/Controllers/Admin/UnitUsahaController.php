<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UnitUsaha;
use App\Models\User; // Pastikan User model di-import
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UnitUsahaController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        // Menghapus 'bungdes' dari eager loading karena relasi sudah dihapus
        $query = UnitUsaha::with(['user']);

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

        // $bungdeses dihapus
        // Hanya tampilkan user dengan role manajer_unit_usaha untuk pilihan penanggung jawab
        $users = User::where('role', 'manajer_unit_usaha')->get();
        // $bungdeses dihapus dari compact
        return view('admin.manajemen_data.unit_usaha.create', compact('users'));
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
            'nama_unit' => 'required|string|max:255|unique:unit_usahas,nama_unit',
            'jenis_usaha' => 'required|string|max:100',
            'tanggal_mulai_operasi' => 'nullable|date',
            'status_operasi' => ['required', 'string', Rule::in(['Aktif', 'Tidak Aktif', 'Dalam Pengembangan'])],
            'user_id' => 'nullable|exists:users,user_id', // Admin bisa menugaskan
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->only([
            'nama_unit',
            'jenis_usaha',
            'tanggal_mulai_operasi',
            'status_operasi',
            'user_id' // Admin BUMDes bisa memilih user_id
        ]);

        if ($request->filled('user_id')) {
            $selectedUser = User::find($request->user_id);
            if (!$selectedUser || !$selectedUser->isManajerUnitUsaha()) {
                return redirect()->back()->withErrors(['user_id' => 'Penanggung Jawab harus memiliki peran Manajer Unit Usaha.'])->withInput();
            }
        } else {
            $data['user_id'] = null;
        }

        UnitUsaha::create($data);

        return redirect()->route('admin.manajemen-data.unit_usaha.index')->with('success', 'Unit usaha berhasil ditambahkan!');
    }

    public function show(UnitUsaha $unitUsaha)
    {
        // Menghapus 'bungdes' dari eager loading
        $unitUsaha->load(['user']);
        return view('admin.manajemen_data.unit_usaha.show', compact('unitUsaha'));
    }

    public function edit(UnitUsaha $unitUsaha)
    {
        $user = Auth::user();

        // Manajer unit usaha hanya bisa mengedit unit usaha yang dia kelola.
        if ($user->isManajerUnitUsaha() && $unitUsaha->user_id !== $user->user_id) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit unit usaha ini.');
        }

        $users = User::where('role', 'manajer_unit_usaha')->get();
        return view('admin.manajemen_data.unit_usaha.edit', compact('unitUsaha', 'users'));
    }

    public function update(Request $request, UnitUsaha $unitUsaha)
    {
        $loggedInUser = Auth::user();

        if ($loggedInUser->isManajerUnitUsaha() && $unitUsaha->user_id !== $loggedInUser->user_id) {
            abort(403, 'Anda tidak memiliki akses untuk memperbarui unit usaha ini.');
        }

        $rules = [
            'nama_unit' => ['required', 'string', 'max:255', Rule::unique('unit_usahas', 'nama_unit')->ignore($unitUsaha->unit_usaha_id, 'unit_usaha_id')],
            'jenis_usaha' => 'required|string|max:100',
            'tanggal_mulai_operasi' => 'nullable|date',
            'status_operasi' => ['required', 'string', Rule::in(['Aktif', 'Tidak Aktif', 'Dalam Pengembangan'])],
        ];

        if ($loggedInUser->isAdminBumdes()) {
            $rules['user_id'] = 'nullable|exists:users,user_id';
        } else {
            $rules['user_id'] = ['nullable', Rule::in([$unitUsaha->user_id])];
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->only([
            'nama_unit',
            'jenis_usaha',
            'tanggal_mulai_operasi',
            'status_operasi',
        ]);

        // Hanya tambahkan user_id ke $data jika admin_bumdes yang mengedit
        if ($loggedInUser->isAdminBumdes()) {
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
            $data['user_id'] = $unitUsaha->user_id;
        }

        $unitUsaha->update($data);

        return redirect()->route('admin.manajemen-data.unit_usaha.index')->with('success', 'Unit usaha berhasil diperbarui!');
    }

    public function destroy(UnitUsaha $unitUsaha)
    {
        if (!Auth::user()->isAdminBumdes()) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus Unit Usaha.');
        }


        $unitUsaha->delete();
        return redirect()->route('admin.manajemen-data.unit_usaha.index')->with('success', 'Unit usaha berhasil dihapus!');
    }
}
