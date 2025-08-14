<?php

namespace App\Http\Controllers\Usaha;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UnitUsaha;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdminUnitUsahaController extends Controller
{
    public function edit(Request $request)
    {
        $unitUsaha = $this->findUnitUsaha($request->unit_usaha_id);

        if (!$unitUsaha) {
            return redirect()->route('home')->with('error', 'Anda tidak memiliki akses ke unit usaha ini.');
        }

        return view('usaha.unit_setting.edit', compact('unitUsaha'));
    }

    public function update(Request $request)
    {
        $unitUsaha = $this->findUnitUsaha($request->unit_usaha_id);

        if (!$unitUsaha) {
            return redirect()->route('home')->with('error', 'Anda tidak memiliki akses ke unit usaha ini.');
        }

        $rules = [
            'nama_unit' => [
                'required', 'string', 'max:255',
                Rule::unique('unit_usahas', 'nama_unit')->ignore($unitUsaha->unit_usaha_id, 'unit_usaha_id')
            ],
            'jenis_usaha' => 'required|string|max:100',
            'tanggal_mulai_operasi' => 'nullable|date',
            'status_operasi' => ['required', 'string', Rule::in(['Aktif', 'Tidak Aktif', 'Dalam Pengembangan'])],
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
        ]);

        $unitUsaha->update($data);

        return redirect()->route('usaha.unit_setting.edit', ['unit_usaha_id' => $unitUsaha->unit_usaha_id])
                         ->with('success', 'Unit usaha berhasil diperbarui!');
    }

    /**
     * Cari unit usaha yang dikelola user
     * @param  int|null  $unitUsahaId
     * @return \App\Models\UnitUsaha|null
     */
   private function findUnitUsaha($unitUsahaId = null)
{
    $user = Auth::user();

    $query = UnitUsaha::whereHas('users', function ($q) use ($user) {
        $q->where('users.user_id', $user->user_id);
    });

    if ($unitUsahaId) {
        $query->where('unit_usaha_id', $unitUsahaId);
    }

    return $query->first();
}

}
