<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Tagihan;
use App\Models\Pelanggan;
use App\Models\Petugas;
use Carbon\Carbon;
use SplFileObject;

class TagihanMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Peta file CSV ke ID petugas, kolom meter, dan periode

   $data_files = [
            'dadang.csv' => [
                'petugas_id' => 1,
                'meter_awal_col' => 'JULI',
                'meter_akhir_col' => 'AGTS',
                'periode' => Carbon::create(null, 8, 1)->toDateString(),
            ],
            'obi.csv' => [
                'petugas_id' => 2,
                'meter_awal_col' => 'AGTS',
                'meter_akhir_col' => 'SEP',
                'periode' => Carbon::create(null, 9, 1)->toDateString(), // September
            ],
            'dindan.csv' => [
                'petugas_id' => 3,
                'meter_awal_col' => 'AGTS',
                'meter_akhir_col' => 'SEPT',
                'periode' => Carbon::create(null, 9, 1)->toDateString(), // September
            ],
            'dindan.csv' => [
                'petugas_id' => 4,
                'meter_awal_col' => 'AGTS',
                'meter_akhir_col' => 'SEP',
                'periode' => Carbon::create(null, 9, 1)->toDateString(), // September
            ],
        ];

        DB::beginTransaction();
        try {
            foreach ($data_files as $filename => $config) {
                $filePath = database_path('seeders/csv/' . $filename);
                if (!file_exists($filePath)) {
                    $this->command->error("File tidak ditemukan: {$filePath}");
                    continue;
                }

                $file = new SplFileObject($filePath);
                $file->setFlags(SplFileObject::READ_CSV | SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
                $file->setCsvControl(',', '"', '\\');

                $header = [];
                $foundHeader = false;
                foreach ($file as $row) {
                    // Cek apakah ini adalah baris header
                    if (!$foundHeader && is_array($row) && in_array('NOMER DATA', array_map('trim', $row))) {
                        $header = array_map('trim', $row);
                        $foundHeader = true;
                        continue;
                    }

                    // Lompat jika header belum ditemukan
                    if (!$foundHeader) {
                        continue;
                    }

                    // Lompat jika baris kosong atau berisi "TOTAL"
                    $nomer_data = $row[1] ?? null;
                    if (empty($nomer_data) || strtolower($nomer_data) === 'total') {
                        continue;
                    }

                    // Lompat jika jumlah kolom tidak sesuai dengan header
                    if (count($row) !== count($header)) {
                        continue;
                    }

                    $data = array_combine($header, $row);

                    $nama_pelanggan = $data['NAMA'] ?? null;
                    $lokasi = $data['LOKASI'] ?? null;
                    $meter_awal = (int) ($data[$config['meter_awal_col']] ?? 0);
                    $meter_akhir = (int) ($data[$config['meter_akhir_col']] ?? 0);
                    $total_pemakaian_m3 = max(0, $meter_akhir - $meter_awal);
                    $adm = (float) ($data['ADM'] ?? 0);
                    $pml = (float) ($data['PML'] ?? 0);
                    $tagihan = (float) ($data['TAGIHAN'] ?? 0);
                    $tunggakan = (float) ($data['TUNGGAKAN'] ?? 0);
                    $denda = (float) ($data['DENDA'] ?? 0);
                    $total = (float) ($data['TOTAL'] ?? 0);

                    // Cari pelanggan berdasarkan nomer data, jika tidak ada, buat baru
                    $pelanggan = Pelanggan::firstOrCreate(
                        ['nomer_pelanggan' => $nomer_data],
                        [
                            'nama' => $nama_pelanggan,
                            'alamat' => $lokasi ?? 'Lokasi tidak diketahui',
                            'petugas_id' => $config['petugas_id'],
                        ]
                    );

                    // Buat atau perbarui record tagihan
                    $tagihan = Tagihan::updateOrCreate(
                        ['pelanggan_id' => $pelanggan->id, 'periode_tagihan' => $config['periode']],
                        [
                            'petugas_id' => $config['petugas_id'],
                            'meter_awal' => $meter_awal,
                            'meter_akhir' => $meter_akhir,
                            'total_pemakaian_m3' => $total_pemakaian_m3,
                            'subtotal_pemakaian' => $tagihan,
                            'biaya_lainnya' => $adm + $pml,
                            'tunggakan' => $tunggakan,
                            'denda' => $denda,
                            'total_harus_dibayar' => $total,
                            'status_pembayaran' => ($tunggakan > 0 || $denda > 0) ? 'Belum Lunas' : 'Lunas',
                            'tanggal_cetak' => Carbon::now(),
                        ]
                    );

                    // Buat rincian tagihan
                    $tagihan->rincian()->delete();
                    $rincian = [];
                    if (!empty($data['H*A1'])) $rincian[] = ['deskripsi' => 'Blok 1', 'kuantitas' => $data['A*1'] ?? 0, 'harga_satuan' => $data['H*A1'] ?? 0, 'subtotal' => $data['H*A1'] ?? 0];
                    if (!empty($data['H*B1'])) $rincian[] = ['deskripsi' => 'Blok 2', 'kuantitas' => $data['B*1'] ?? 0, 'harga_satuan' => $data['H*B1'] ?? 0, 'subtotal' => $data['H*B1'] ?? 0];
                    if (!empty($data['H*C1'])) $rincian[] = ['deskripsi' => 'Blok 3', 'kuantitas' => $data['C*1'] ?? 0, 'harga_satuan' => $data['H*C1'] ?? 0, 'subtotal' => $data['H*C1'] ?? 0];
                    if (!empty($data['H*D1'])) $rincian[] = ['deskripsi' => 'Blok 4', 'kuantitas' => $data['D*1'] ?? 0, 'harga_satuan' => $data['H*D1'] ?? 0, 'subtotal' => $data['H*D1'] ?? 0];
                    if (!empty($adm)) $rincian[] = ['deskripsi' => 'Biaya Administrasi', 'kuantitas' => 1, 'harga_satuan' => $adm, 'subtotal' => $adm];
                    if (!empty($pml)) $rincian[] = ['deskripsi' => 'Biaya Pemeliharaan', 'kuantitas' => 1, 'harga_satuan' => $pml, 'subtotal' => $pml];
                    if (!empty($tunggakan)) $rincian[] = ['deskripsi' => 'Tunggakan', 'kuantitas' => 1, 'harga_satuan' => $tunggakan, 'subtotal' => $tunggakan];
                    if (!empty($denda)) $rincian[] = ['deskripsi' => 'Denda', 'kuantitas' => $data['JML DENDA'] ?? 0, 'harga_satuan' => 5000, 'subtotal' => $denda];

                    if (!empty($rincian)) {
                        $tagihan->rincian()->createMany($rincian);
                    }
                }
            }
            DB::commit();
            $this->command->info('Data tagihan berhasil di-seed.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Gagal melakukan seeding: ' . $e->getMessage());
        }
    }
}
