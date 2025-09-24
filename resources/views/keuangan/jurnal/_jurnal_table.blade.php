{{-- Ini adalah isi dari file baru: resources/views/keuangan/jurnal/_jurnal_table.blade.php --}}

{{-- Bagian Total Debit/Kredit dan Status --}}


{{-- Bagian Tabel --}}
<div class="table-responsive mt-3">
    <table class="table table-hover table-bordered mb-0">
        <thead class="bg-light">
            <tr>
                <th style="width: 15%;">Tanggal</th>
                <th>Keterangan / Akun</th>
                <th class="text-right">Debit</th>
                <th class="text-right">Kredit</th>
                <th class="text-center" style="width: 10%;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($jurnals as $jurnal)
                <tr class="table-primary">
                    <td><strong>{{ \Carbon\Carbon::parse($jurnal->tanggal_transaksi)->isoFormat('DD MMM YYYY') }}</strong></td>
                    <td>
                        <strong>{{ $jurnal->deskripsi }}</strong>
                        <br>
                        @if($jurnal->unitUsaha)
                            <span class="badge badge-info">{{ $jurnal->unitUsaha->nama_unit }}</span>
                        @else
                            <span class="badge badge-secondary">BUMDes Pusat</span>
                        @endif
                        @switch($jurnal->status)
                            @case('menunggu')
                                <span class="badge badge-warning">Menunggu</span>
                                @break
                            @case('disetujui')
                                <span class="badge badge-success">Disetujui</span>
                                @break
                            @case('ditolak')
                                <span class="badge badge-danger">Ditolak</span>
                                @if($jurnal->rejected_reason)
                                    <div class="mt-1 text-danger small">
                                        <strong>Alasan:</strong> {{ $jurnal->rejected_reason }}
                                    </div>
                                @endif
                                @break
                        @endswitch
                    </td>
                    <td class="text-right"><strong>Rp {{ number_format($jurnal->total_debit, 0, ',', '.') }}</strong></td>
                    <td class="text-right"><strong>Rp {{ number_format($jurnal->total_kredit, 0, ',', '.') }}</strong></td>
                    <td class="text-center">
                        @php
                            $canEditOrDelete = auth()->user()->hasAnyRole(['admin_bumdes', 'bendahara_bumdes', 'direktur_bumdes', 'sekretaris_bumdes']) ||
                                (auth()->user()->hasAnyRole(['admin_unit_usaha', 'manajer_unit_usaha']) &&
                                auth()->user()->unitUsahas->pluck('unit_usaha_id')->contains($jurnal->unit_usaha_id));
                        @endphp
                        @if($canEditOrDelete && $jurnal->status != 'disetujui')
                            <a href="{{ route('jurnal-umum.edit', $jurnal->jurnal_id) }}" class="btn btn-info btn-xs" title="Edit Jurnal">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="btn btn-danger btn-xs" title="Hapus Jurnal" data-toggle="modal" data-target="#deleteModal" data-id="{{ $jurnal->jurnal_id }}">
                                <i class="fas fa-trash"></i>
                            </button>
                        @elseif ($jurnal->status == 'disetujui' && auth()->user()->hasAnyRole(['admin_bumdes', 'bendahara_bumdes', 'direktur_bumdes', 'sekretaris_bumdes']))
                             <a href="{{ route('jurnal-umum.edit', $jurnal->jurnal_id) }}" class="btn btn-info btn-xs" title="Edit Jurnal (akan mereset status)">
                                 <i class="fas fa-edit"></i>
                             </a>
                        @endif
                    </td>
                </tr>
                @foreach ($jurnal->detailJurnals as $detail)
                    <tr>
                        <td></td>
                        <td style="{{ $detail->kredit > 0 ? 'padding-left: 30px;' : '' }}">
                            [{{ $detail->akun->kode_akun }}] {{ $detail->akun->nama_akun }}
                            @if($detail->keterangan)
                                <br><small class="text-muted"><em>{{ $detail->keterangan }}</em></small>
                            @endif
                        </td>
                        <td class="text-right">{{ $detail->debit > 0 ? 'Rp ' . number_format($detail->debit, 0, ',', '.') : '' }}</td>
                        <td class="text-right">{{ $detail->kredit > 0 ? 'Rp ' . number_format($detail->kredit, 0, ',', '.') : '' }}</td>
                        <td></td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="5" class="text-center">Tidak ada data jurnal yang cocok dengan filter.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="card-footer clearfix">
    {{ $jurnals->links() }}
</div>