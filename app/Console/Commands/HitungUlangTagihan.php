<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tagihan;
use App\Http\Controllers\Usaha\TagihanController;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use ReflectionClass;

class HitungUlangTagihan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tagihan:hitung-ulang {--bulan=} {--tahun=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menghitung ulang total tagihan untuk periode tertentu yang datanya diimpor dari Excel';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $bulan = $this->option('bulan') ?? date('n');
        $tahun = $this->option('tahun') ?? date('Y');
        $this->info("Memulai proses hitung ulang tagihan untuk periode: {$bulan}-{$tahun}...");
        $periode = Carbon::create($tahun, $bulan, 1);
        $tagihanUntukDihitung = Tagihan::where('periode_tagihan', $periode->toDateString())->get(); // Kita hitung ulang semua, bukan hanya yang totalnya 0

        if ($tagihanUntukDihitung->isEmpty()) {
            $this->warn("Tidak ada tagihan yang perlu dihitung ulang pada periode ini.");
            return 0;
        }

        $progressBar = $this->output->createProgressBar($tagihanUntukDihitung->count());
        $progressBar->start();

        $controller = new TagihanController();
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('kalkulasiTagihanData');
        $method->setAccessible(true);

        DB::beginTransaction();
        try {
            foreach ($tagihanUntukDihitung as $tagihan) {
                $total_pemakaian_real = max(0, $tagihan->meter_akhir - $tagihan->meter_awal);
                
                $hasil_kalkulasi = $method->invoke(
                    $controller,
                    $total_pemakaian_real,
                    $tagihan->tunggakan,
                    Carbon::parse($tagihan->periode_tagihan)
                );

                // ==========================================================
                // --- TAMBAHKAN KODE INI ---
                // Logika untuk menghapus rincian lama dan menyimpan rincian baru
                $tagihan->rincian()->delete();
                if (!empty($hasil_kalkulasi['rincian_dihitung'])) {
                    $tagihan->rincian()->createMany($hasil_kalkulasi['rincian_dihitung']);
                }
                // --- AKHIR DARI KODE TAMBAHAN ---
                // ==========================================================

                // Update data utama di tabel tagihan
                $tagihan->total_pemakaian_m3 = $total_pemakaian_real;
                $tagihan->subtotal_pemakaian = $hasil_kalkulasi['subtotal_pemakaian'];
                $tagihan->biaya_lainnya = $hasil_kalkulasi['biaya_lainnya'];
                $tagihan->denda = $hasil_kalkulasi['denda'];
                $tagihan->total_harus_dibayar = $hasil_kalkulasi['total_harus_dibayar'];
                
                if ($hasil_kalkulasi['total_harus_dibayar'] == 0 && $total_pemakaian_real == 0) {
                     $tagihan->status_pembayaran = 'Lunas';
                } else {
                     $tagihan->status_pembayaran = 'Belum Lunas';
                }
                $tagihan->save();

                $progressBar->advance();
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("\nTerjadi error: " . $e->getMessage());
            return 1;
        }

        $progressBar->finish();
        $this->info("\nSelesai! " . $tagihanUntukDihitung->count() . " tagihan berhasil dihitung ulang.");
        return 0;
    }
}