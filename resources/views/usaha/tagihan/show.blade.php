@extends('adminlte::page')

@section('title', 'Detail Tagihan')

@section('content_header')
    <h1 class="m-0 text-dark">Detail Tagihan #{{ $tagihan->id }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            {{-- Tombol Aksi (Hanya tampil di layar) --}}
            <div class="mb-3 d-print-none">
                <a href="{{ route('usaha.tagihan.index') }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Kembali</a>
                <button onclick="window.print()" class="btn btn-primary"><i class="fa fa-print"></i> Cetak</button>
            </div>

            {{-- Area Struk yang akan dicetak --}}
            <div class="invoice p-3 mb-3" id="struk-area">
                {{-- Header Struk --}}
                <div class="text-center">
                    <h5>BADAN USAHA MILIK DESA</h5>
                    <h6>UNIT SPAM DESA KSM TIRTA SARANA SEJAHTERA</h6>
                    <small>DESA SINDANGRAJA KEC. SUKALUYU KAB. CIANJUR</small>
                    <hr style="border-top: 1px dashed black;">
                    <h6 class="font-weight-bold"><u>BUKTI PEMBAYARAN TAGIHAN AIR BERSIH</u></h6>
                </div>
                <br>

                {{-- Informasi Pelanggan --}}
                <div class="row invoice-info">
                    <div class="col-sm-6 invoice-col">
                        <address>
                            <strong>Nama: {{ $tagihan->pelanggan->nama }}</strong><br>
                            Alamat: {{ $tagihan->pelanggan->alamat }}<br>
                            Periode Penggunaan: {{ \Carbon\Carbon::parse($tagihan->periode_tagihan)->isoFormat('MMMM YYYY') }}<br>
                            Dibayarkan di Bulan: {{ \Carbon\Carbon::parse($tagihan->tanggal_cetak)->isoFormat('MMMM YYYY') }}
                        </address>
                    </div>
                    <div class="col-sm-6 invoice-col text-right">
                         <strong>Meter Awal: {{ $tagihan->meter_awal }} m³</strong><br>
                         <strong>Meter Akhir: {{ $tagihan->meter_akhir }} m³</strong><br>
                         <strong>Total Pemakaian: {{ $tagihan->total_pemakaian_m3 }} m³</strong><br>
                    </div>
                </div>

                {{-- Tabel Rincian Pembayaran --}}
                <div class="row mt-4">
                    <div class="col-12 table-responsive">
                        <h6><u>RINCIAN PEMBAYARAN AIR</u></h6>
                        <table class="table table-sm">
                            <tbody>
                                @foreach ($tagihan->rincian as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}. {{ $item->deskripsi }}</td>
                                    <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                                <tr>
                                    <th class="text-uppercase">Jumlah Total yg Harus Dibayar</th>
                                    <th class="text-right">Rp {{ number_format($tagihan->total_harus_dibayar, 0, ',', '.') }}</th>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                 {{-- Footer Struk --}}
                <div class="row mt-5">
                    <div class="col-6">
                        Petugas Penagihan<br><br><br>
                        <strong>{{ $tagihan->petugas->nama_petugas ?? '(Belum Ditentukan)' }}</strong>
                    </div>
                    <div class="col-6 text-center">
                        Sindangraja, {{ \Carbon\Carbon::parse($tagihan->tanggal_cetak)->isoFormat('DD MMMM YYYY') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@push('css')
<style>
    /* CSS ini akan aktif saat halaman dicetak */
    @media print {
        /* Sembunyikan semua elemen kecuali area struk */
        body * {
            visibility: hidden;
        }
        #struk-area, #struk-area * {
            visibility: visible;
        }
        #struk-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        /* Hapus margin dan padding default dari AdminLTE */
        .content-wrapper, .content {
            margin: 0 !important;
            padding: 0 !important;
        }
        .invoice {
            font-size: 12px !important;
            font-family: 'Courier New', Courier, monospace !important;
        }
    }
</style>
@endpush
