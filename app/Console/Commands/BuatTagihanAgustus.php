<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tagihan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BuatTagihanAgustus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tagihan:buat-agustus {--tahun=2025}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat data tagihan Agustus berdasarkan data meter awal September yang sudah ada';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tahun = $this->option('tahun');
        $bulanSeptember = 9;
        $bulanAgustus = 8;

        $this->info("Mencari data September {$tahun} untuk membuat data Agustus {$tahun}...");

        $periodeSeptember = Carbon::create($tahun, $bulanSeptember, 1)->toDateString();
        $periodeAgustus = Carbon::create($tahun, $bulanAgustus, 1)->toDateString();

        // Ambil semua tagihan dari bulan September
        $tagihanSeptember = Tagihan::where('periode_tagihan', $periodeSeptember)->get();

        if ($tagihanSeptember->isEmpty()) {
            $this->error("Data tagihan untuk September {$tahun} tidak ditemukan. Pastikan data September sudah ada dan tanggalnya benar ('{$periodeSeptember}').");
            return 1;
        }

        $progressBar = $this->output->createProgressBar($tagihanSeptember->count());
        $progressBar->start();

        $jumlahDibuat = 0;
        DB::beginTransaction();
        try {
            foreach ($tagihanSeptember as $tagihanSep) {
                // Gunakan updateOrCreate untuk menghindari duplikasi jika command dijalankan lagi
                Tagihan::updateOrCreate(
                    [
                        'pelanggan_id'    => $tagihanSep->pelanggan_id,
                        'periode_tagihan' => $periodeAgustus,
                    ],
                    [
                        'petugas_id'          => $tagihanSep->petugas_id, // Gunakan petugas yang sama
                        'meter_awal'          => 0, // Kita tidak tahu meter awalnya, jadi kita set 0 atau bisa juga dikira-kira.
                        
                        // Nilai terpenting: meter akhir Agustus adalah meter awal September
                        'meter_akhir'         => $tagihanSep->meter_awal, 
                        
                        'total_pemakaian_m3'  => $tagihanSep->meter_awal, // Pemakaian dianggap dari 0
                        'total_harus_dibayar' => 0, // Biarkan 0, nanti dihitung ulang jika perlu
                        'status_pembayaran'   => 'Lunas', // Asumsikan tagihan bulan lalu sudah lunas
                        'tanggal_cetak'       => now(),
                    ]
                );
                $jumlahDibuat++;
                $progressBar->advance();
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("\nTerjadi error: " . $e->getMessage());
            return 1;
        }

        $progressBar->finish();
        $this->info("\nSelesai! Berhasil membuat {$jumlahDibuat} data tagihan untuk Agustus {$tahun}.");
        $this->warn("Anda mungkin perlu menjalankan 'php artisan tagihan:hitung-ulang --bulan=8 --tahun={$tahun}' untuk mengkalkulasi total bayar Agustus jika diperlukan.");

        return 0;
    }
}