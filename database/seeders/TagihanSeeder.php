<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tagihan;
use App\Models\Pelanggan;
use App\Models\Petugas;
use App\Models\Tarif;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TagihanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tagihan::query()->delete();

        $customers = Pelanggan::all()->keyBy(function ($customer) {
            return $customer->nama . '|' . $customer->alamat;
        });

        $petugasMap = [
            'KOLEKTOR DINDAN' => Petugas::where('nama_petugas', 'KOLEKTOR DINDAN')->first()->id,
            'KOLEKTOR OBI' => Petugas::where('nama_petugas', 'KOLEKTOR OBI')->first()->id,
            'KOLEKTOR DADANG' => Petugas::where('nama_petugas', 'KOLEKTOR DADANG')->first()->id,
        ];

        $files = [
            '5_KOLEKTOR DINDAN_1_SEPTEMBER_25.csv' => 'KOLEKTOR DINDAN',
            '3_KOLEKTOR DINDAN 1_SEPTEMBER_25.csv' => 'KOLEKTOR DINDAN',
            '1_KOLEKTOR OBI_SEPTEMBER_25.csv' => 'KOLEKTOR OBI',
            '2_KOLEKTOR DADANG_SEPTEMBER_25.csv' => 'KOLEKTOR DADANG'
        ];

        $periodeTagihan = '2025-09-01'; // September 2025

        foreach ($files as $file => $collectorName) {
            $path = database_path("seeders/csv/{$file}");

            if (!file_exists($path)) {
                $this->command->error("File not found: {$path}");
                continue;
            }

            $csvData = array_map('str_getcsv', file($path));
            $headers = array_shift($csvData);

            // Get column indices
            $colIndices = $this->getColumnIndices($headers);

            foreach ($csvData as $row) {
                // Skip invalid rows
                if (empty($row[3]) || empty($row[4]) || $row[0] === ' ' || !is_numeric($row[0])) {
                    continue;
                }

                $nama = trim($row[3]);
                $alamat = trim($row[4]);
                $customerKey = $nama . '|' . $alamat;

                if (!isset($customers[$customerKey])) {
                    continue;
                }

                $pelangganId = $customers[$customerKey]->id;
                $petugasId = $petugasMap[$collectorName];

                // Get meter readings
                $meterAwal = $this->getSafeValue($row, $colIndices, 'meter_awal');
                $meterAkhir = $this->getSafeValue($row, $colIndices, 'meter_akhir');

                // Calculate usage
                $totalPemakaianReal = max(0, $meterAkhir - $meterAwal);

                // Get outstanding amount from previous period
                $tunggakan = $this->getSafeValue($row, $colIndices, 'TUNGGAKAN');

                // Calculate the bill using the same logic as the controller
                $hasilKalkulasi = $this->kalkulasiTagihanData(
                    $totalPemakaianReal,
                    $tunggakan,
                    Carbon::parse($periodeTagihan)
                );

                // Prepare data for saving
                $dataUntukDisimpan = array_merge($hasilKalkulasi, [
                    'pelanggan_id' => $pelangganId,
                    'periode_tagihan' => $periodeTagihan,
                    'petugas_id' => $petugasId,
                    'meter_awal' => $meterAwal,
                    'meter_akhir' => $meterAkhir,
                    'tanggal_cetak' => now(),
                    'status_pembayaran' => ($hasilKalkulasi['total_harus_dibayar'] == 0) ? 'Lunas' : 'Belum Lunas',
                ]);

                // Remove rincian_dihitung as it's not a column in tagihan table
                unset($dataUntukDisimpan['rincian_dihitung']);

                // Create or update the tagihan
                $tagihan = Tagihan::updateOrCreate(
                    ['pelanggan_id' => $pelangganId, 'periode_tagihan' => $periodeTagihan],
                    $dataUntukDisimpan
                );

                // Save the billing details (rincian)
                $tagihan->rincian()->delete();
                $tagihan->rincian()->createMany($hasilKalkulasi['rincian_dihitung']);
            }
        }

        $this->command->info('Tagihan seeded successfully.');
    }

    /**
     * Get column indices with multiple fallbacks
     */
    private function getColumnIndices(array $headers): array
    {
        $indices = [];
        $cleanHeaders = array_map('trim', $headers);

        // Meter readings - multiple possible column names
        $meterAwalCandidates = ['AGTS', 'JULI'];
        $meterAkhirCandidates = ['SEP', 'SEPT', 'AGTS'];

        foreach ($meterAwalCandidates as $candidate) {
            $index = array_search($candidate, $cleanHeaders);
            if ($index !== false) {
                $indices['meter_awal'] = $index;
                break;
            }
        }

        foreach ($meterAkhirCandidates as $candidate) {
            $index = array_search($candidate, $cleanHeaders);
            if ($index !== false) {
                $indices['meter_akhir'] = $index;
                break;
            }
        }

        // Other columns
        $columnMap = [
            'TUNGGAKAN' => ['TUNGGAKAN'],
        ];

        foreach ($columnMap as $key => $candidates) {
            foreach ($candidates as $candidate) {
                $index = array_search($candidate, $cleanHeaders);
                if ($index !== false) {
                    $indices[$key] = $index;
                    break;
                }
            }
        }

        return $indices;
    }

    /**
     * Safely get value from row with fallback
     */
    private function getSafeValue(array $row, array $colIndices, string $key): float
    {
        if (!isset($colIndices[$key]) || !isset($row[$colIndices[$key]])) {
            return 0.0;
        }

        return $this->parseNumeric($row[$colIndices[$key]]);
    }

    /**
     * Parse numeric value from string
     */
    private function parseNumeric($value): float
    {
        if (empty($value) || $value === ' ') {
            return 0.0;
        }

        // Remove thousand separators and spaces
        $cleaned = str_replace(['.', ' ', ','], ['', '', '.'], $value);

        return (float) $cleaned;
    }

    /**
     * Calculate billing data (copied from controller and adapted)
     */
    private function kalkulasiTagihanData($total_pemakaian_real, $tunggakan_manual, $periode_tagihan)
    {
        $semua_tarif = Tarif::all();
        $rincian_dihitung = [];
        $subtotal_pemakaian = 0;

        // ===== Hitung tarif pemakaian =====
        $tarif_pemakaian = $semua_tarif->where('jenis_tarif', 'pemakaian')
            ->sortBy('batas_bawah')
            ->values();

        $sisa = $total_pemakaian_real;
        $firstBlock = true;

        foreach ($tarif_pemakaian as $tarif) {
            $bawah = isset($tarif->batas_bawah) ? (int)$tarif->batas_bawah : 0;
            $atas = isset($tarif->batas_atas) ? (int)$tarif->batas_atas : null;

            if ($firstBlock) {
                if ($total_pemakaian_real > 0 && $total_pemakaian_real <= $tarif->batas_atas) {
                    $kuantitas = $tarif->batas_atas;
                    $sisa = 0;
                } else {
                    $kuantitas = min($sisa, $tarif->batas_atas);
                    $sisa -= $kuantitas;
                }
                $firstBlock = false;
            } else {
                if ($sisa <= 0) break;
                $kuantitas = min($sisa, ($atas - ($bawah > 0 ? ($bawah - 1) : 0)));
                $sisa -= $kuantitas;
            }

            if ($kuantitas > 0) {
                $subtotal = $kuantitas * $tarif->harga;
                $rincian_dihitung[] = [
                    'deskripsi' => $tarif->deskripsi,
                    'kuantitas' => (int)$kuantitas,
                    'harga_satuan' => $tarif->harga,
                    'subtotal' => $subtotal,
                ];
                $subtotal_pemakaian += $subtotal;
            }
        }

        // ===== Biaya tetap =====
        $biaya_lainnya = 0;
        $tarif_biaya_tetap = $semua_tarif->where('jenis_tarif', 'biaya_tetap');
        foreach ($tarif_biaya_tetap as $biaya) {
            $biaya_lainnya += $biaya->harga;
            $rincian_dihitung[] = [
                'deskripsi' => $biaya->deskripsi,
                'kuantitas' => 1,
                'harga_satuan' => $biaya->harga,
                'subtotal' => $biaya->harga,
            ];
        }

        // ===== Hitung denda dinamis =====
        $denda_per_bulan = 5000;
        $denda = 0;

        $bulanTagihan = $periode_tagihan->startOfMonth();
        $bulanSekarang = Carbon::now()->startOfMonth();
        $selisihBulan = $bulanTagihan->diffInMonths($bulanSekarang);

        // Hanya terapkan denda jika sudah terlambat 1 bulan atau lebih
        if ($selisihBulan > 0) {
            $denda = $selisihBulan * $denda_per_bulan;
            $rincian_dihitung[] = [
                'deskripsi' => 'Denda Keterlambatan',
                'kuantitas' => $selisihBulan,
                'harga_satuan' => $denda_per_bulan,
                'subtotal' => $denda,
            ];
        }

        // ===== Tambahkan tunggakan manual (kalau ada) =====
        if ($tunggakan_manual > 0) {
            $rincian_dihitung[] = [
                'deskripsi' => 'Tunggakan Bulan Sebelumnya',
                'kuantitas' => 1,
                'harga_satuan' => $tunggakan_manual,
                'subtotal' => $tunggakan_manual,
            ];
        }

        // ===== Total =====
        $total_harus_dibayar = $subtotal_pemakaian + $biaya_lainnya + $tunggakan_manual + $denda;

        return [
            'total_pemakaian_m3' => $total_pemakaian_real,
            'subtotal_pemakaian' => $subtotal_pemakaian,
            'biaya_lainnya' => $biaya_lainnya,
            'denda' => $denda,
            'tunggakan' => $tunggakan_manual,
            'total_harus_dibayar' => $total_harus_dibayar,
            'rincian_dihitung' => $rincian_dihitung,
        ];
    }
}
