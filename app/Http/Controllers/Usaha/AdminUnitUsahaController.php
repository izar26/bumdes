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
    public function edit()
    {
        $unitUsaha = $this->findUnitUsaha();

        if (!$unitUsaha) {
            return redirect()->route('admin.dashboard')->with('error', 'Anda tidak terhubung dengan Unit Usaha mana pun.');
        }

        return view('usaha.unit_setting.edit', compact('unitUsaha'));
    }

    public function update(Request $request)
    {
        $unitUsaha = $this->findUnitUsaha();

        if (!$unitUsaha) {
            return redirect()->route('admin.dashboard')->with('error', 'Anda tidak terhubung dengan Unit Usaha mana pun.');
        }

        $rules = [
            'nama_unit' => ['required', 'string', 'max:255', Rule::unique('unit_usahas', 'nama_unit')->ignore($unitUsaha->unit_usaha_id, 'unit_usaha_id')],
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

        return redirect()->route('usaha.unit_setting.edit')->with('success', 'Unit usaha berhasil diperbarui!');
    }

    private function findUnitUsaha()
    {
        $user = Auth::user();
        return UnitUsaha::where('user_id', $user->user_id)->first();
    }
}
