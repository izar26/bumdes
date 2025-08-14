<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Akun;
use App\Models\JurnalUmum;
use App\Models\UnitUsaha;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $unitUsahaId = null;

        if ($user->hasRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
            // Pastikan Anda sudah membuat relasi ini di model User.php
            // public function managedUnit() { return $this->hasOne(UnitUsaha::class, 'user_id', 'user_id'); }
            $unit = $user->managedUnit;
            if ($unit) {
                $unitUsahaId = $unit->unit_usaha_id;
            }
        }

        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $totalPendapatanBulanIni = $this->calculateTotalByType('Pendapatan', $startDate, $endDate, $unitUsahaId);
        $totalBebanBulanIni = $this->calculateTotalByType('Beban', $startDate, $endDate, $unitUsahaId);
        $labaBulanIni = $totalPendapatanBulanIni - $totalBebanBulanIni;
        $totalKasBank = $this->calculateTotalSaldoKasBank($unitUsahaId);
        $grafikData = $this->getGrafikData($unitUsahaId);
        $kinerjaUnitUsaha = $this->getKinerjaUnitUsaha($startDate, $endDate, $unitUsahaId);
        $notifications = $this->getNotifications();

        return view('admin.dashboard', compact(
            'totalPendapatanBulanIni',
            'totalBebanBulanIni',
            'labaBulanIni',
            'totalKasBank',
            'grafikData',
            'kinerjaUnitUsaha',
            'notifications'
        ));
    }

    private function calculateTotalByType($tipe, $startDate, $endDate, $unitUsahaId = null)
    {
        $query = DB::table('detail_jurnals')
            ->join('akuns', 'detail_jurnals.akun_id', '=', 'akuns.akun_id')
            ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
            ->where('akuns.tipe_akun', $tipe)
            ->where('jurnal_umums.status', 'disetujui')
            ->whereBetween('jurnal_umums.tanggal_transaksi', [$startDate, $endDate]);

        if ($unitUsahaId) {
            $query->where('jurnal_umums.unit_usaha_id', $unitUsahaId);
        }

        if ($tipe == 'Pendapatan' || $tipe == 'Ekuitas' || $tipe == 'Kewajiban') {
            return $query->sum(DB::raw('detail_jurnals.kredit - detail_jurnals.debit'));
        }
        return $query->sum(DB::raw('detail_jurnals.debit - detail_jurnals.kredit'));
    }

    private function calculateTotalSaldoKasBank($unitUsahaId = null)
    {
        $query = DB::table('detail_jurnals')
            ->join('akuns', 'detail_jurnals.akun_id', '=', 'akuns.akun_id')
            ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
            ->where('jurnal_umums.status', 'disetujui')
            ->where('akuns.kode_akun', 'like', '1.1.01.%');

        if ($unitUsahaId) {
            $query->where('jurnal_umums.unit_usaha_id', $unitUsahaId);
        }
        return $query->sum(DB::raw('detail_jurnals.debit - detail_jurnals.kredit'));
    }

    private function getGrafikData($unitUsahaId = null)
    {
        $labels = [];
        $pendapatanData = [];
        $bebanData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $labels[] = $date->isoFormat('MMM');
            $start = $date->copy()->startOfMonth();
            $end = $date->copy()->endOfMonth();
            $pendapatanData[] = $this->calculateTotalByType('Pendapatan', $start, $end, $unitUsahaId);
            $bebanData[] = $this->calculateTotalByType('Beban', $start, $end, $unitUsahaId);
        }
        return ['labels' => $labels, 'pendapatan' => $pendapatanData, 'beban' => $bebanData];
    }

    private function getKinerjaUnitUsaha($startDate, $endDate, $unitUsahaId = null)
    {
        $query = UnitUsaha::query();
        if ($unitUsahaId) {
            $query->where('unit_usaha_id', $unitUsahaId);
        }
        $units = $query->get();
        $kinerja = [];
        foreach ($units as $unit) {
            $pendapatan = $this->calculateTotalByType('Pendapatan', $startDate, $endDate, $unit->unit_usaha_id);
            $beban = $this->calculateTotalByType('Beban', $startDate, $endDate, $unit->unit_usaha_id);
            $kinerja[] = [
                'nama' => $unit->nama_unit,
                'status' => $unit->status_operasi,
                'pendapatan' => $pendapatan,
                'beban' => $beban,
                'laba' => $pendapatan - $beban,
            ];
        }
        return $kinerja;
    }

    private function getNotifications()
    {
        $notifications = [];
        $user = Auth::user();

        // --- AWAL MODIFIKASI NOTIFIKASI ---

        // 1. Notifikasi TUGAS untuk APPROVER
        // Untuk Manajer Unit Usaha: Cek jurnal 'menunggu' yang dibuat oleh 'admin_unit_usaha' di unitnya.
        if ($user->hasRole('manajer_unit_usaha')) {
            $unit = $user->managedUnit;
            if ($unit) {
                $jurnalMenunggu = JurnalUmum::where('status', 'menunggu')
                                            ->where('unit_usaha_id', $unit->unit_usaha_id)
                                            ->whereHas('user.roles', function ($query) {
                                                $query->where('name', 'admin_unit_usaha');
                                            })
                                            ->count();
                if ($jurnalMenunggu > 0) {
                    $notifications[] = [
                        'type' => 'info',
                        'icon' => 'fas fa-file-invoice-dollar',
                        'message' => "Ada <strong>{$jurnalMenunggu} jurnal unit</strong> perlu persetujuan Anda. <a href='".route('approval-jurnal.index')."'>Proses sekarang</a>."
                    ];
                }
            }
        }

        // Untuk Direktur BUMDes: Cek SEMUA jurnal 'menunggu' yang dibuat oleh 'bendahara_bumdes'.
        if ($user->hasRole('direktur_bumdes')) {
            $jurnalMenunggu = JurnalUmum::where('status', 'menunggu')
                                        // PERBAIKAN: Hapus 'whereNull' agar semua jurnal bendahara terhitung
                                        ->whereHas('user.roles', function ($query) {
                                            $query->where('name', 'bendahara_bumdes');
                                        })
                                        ->count();
            if ($jurnalMenunggu > 0) {
                $notifications[] = [
                    'type' => 'info',
                    'icon' => 'fas fa-file-invoice-dollar',
                    'message' => "Ada <strong>{$jurnalMenunggu} jurnal</strong> perlu persetujuan Anda. <a href='".route('approval-jurnal.index')."'>Proses sekarang</a>."
                ];
            }
        }

        // 2. Notifikasi INFO untuk PEMBUAT JURNAL
        // Notifikasi Jurnal DITOLAK
        $jurnalDitolak = JurnalUmum::where('status', 'ditolak')
                                    ->where('user_id', $user->user_id)
                                    ->count();
        if ($jurnalDitolak > 0) {
            $notifications[] = [
                'type' => 'danger',
                'icon' => 'fas fa-exclamation-triangle',
                'message' => "Terdapat <strong>{$jurnalDitolak} jurnal</strong> Anda yang ditolak. <a href='".route('jurnal-umum.index')."'>Periksa & perbaiki</a>."
            ];
        }

        // Notifikasi Jurnal DISETUJUI (hanya yang disetujui dalam 24 jam terakhir)
        $jurnalDisetujui = JurnalUmum::where('status', 'disetujui')
                                    ->where('user_id', $user->user_id)
                                    ->where('approved_at', '>=', Carbon::now()->subDay())
                                    ->count();
        if ($jurnalDisetujui > 0) {
            $notifications[] = [
                'type' => 'success',
                'icon' => 'fas fa-check-circle',
                'message' => "Sebanyak <strong>{$jurnalDisetujui} jurnal</strong> Anda telah disetujui."
            ];
        }

        // --- AKHIR MODIFIKASI NOTIFIKASI ---

        return $notifications;
    }
}
