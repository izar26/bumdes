@extends('adminlte::page')

@section('title', 'Approval Jurnal')

@section('content_header')
    <h1>Approval Jurnal</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <div class="row w-100 align-items-end">
            <div class="col-md-12">
                <form method="GET" class="form-inline justify-content-between">
                    <div class="form-group mr-2">
                        <label class="mr-2">Status</label>
                        <select name="status" class="form-control mr-2" onchange="this.form.submit()">
                            <option value="">Semua</option>
                            <option value="menunggu" {{ request('status')=='menunggu'?'selected':'' }}>Menunggu</option>
                            <option value="disetujui" {{ request('status')=='disetujui'?'selected':'' }}>Disetujui</option>
                            <option value="ditolak" {{ request('status')=='ditolak'?'selected':'' }}>Ditolak</option>
                        </select>
                    </div>
                    @if(isset($unitUsahas) && auth()->user()->hasRole(['admin_bumdes','bendahara_bumdes']))
                    <div class="form-group mr-2">
                        <label class="mr-2">Unit Usaha</label>
                        <select name="unit_usaha_id" class="form-control mr-2" onchange="this.form.submit()">
                            <option value="">Semua</option>
                            @foreach($unitUsahas as $unit)
                                <option value="{{ $unit->unit_usaha_id }}" {{ request('unit_usaha_id')==$unit->unit_usaha_id?'selected':'' }}>
                                    {{ $unit->nama_unit }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="form-group mr-2">
                        <label class="mr-2">Tahun</label>
                        <select name="year" class="form-control mr-2" onchange="this.form.submit()">
                            <option value="">Semua</option>
                            @for($y = date('Y'); $y >= 2020; $y--)
                                <option value="{{ $y }}" {{ request('year')==$y?'selected':'' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="form-group mr-2">
                        <label class="mr-2">Tanggal Mulai</label>
                        <input type="date" name="start_date" class="form-control mr-2" value="{{ request('start_date') }}">
                    </div>
                    <div class="form-group mr-2">
                        <label class="mr-2">Tanggal Akhir</label>
                        <input type="date" name="end_date" class="form-control mr-2" value="{{ request('end_date') }}">
                    </div>
                    <div class="form-group mr-2">
                        <a href="{{ route('approval-jurnal.index') }}" class="btn btn-secondary">Reset Filter</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover table-striped table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>Tanggal</th>
                    <th>Deskripsi</th>
                    <th>Unit Usaha</th>
                    <th class="text-right">Total Debit</th>
                    <th class="text-right">Total Kredit</th>
                    <th>Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($jurnals as $jurnal)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($jurnal->tanggal_transaksi)->format('d M Y') }}</td>
                    <td>{{ $jurnal->deskripsi }}</td>
                    <td>{{ $jurnal->unitUsaha->nama_unit ?? 'BUMDes Pusat' }}</td>
                    <td class="text-right">Rp {{ number_format($jurnal->total_debit, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($jurnal->total_kredit, 0, ',', '.') }}</td>
                    <td>
                        @if($jurnal->status === 'menunggu')
                            <span class="badge badge-warning">Menunggu</span>
                        @elseif($jurnal->status === 'disetujui')
                            <span class="badge badge-success">Disetujui</span>
                        @elseif($jurnal->status === 'ditolak')
                            <span class="badge badge-danger" title="Alasan: {{ $jurnal->rejected_reason }}">Ditolak</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($jurnal->status === 'menunggu')
                            <form action="{{ route('approval-jurnal.approve', $jurnal->jurnal_id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Approve</button>
                            </form>
                            <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#rejectModal{{ $jurnal->jurnal_id }}">
                                <i class="fas fa-times"></i> Reject
                            </button>
                        @endif
                    </td>
                </tr>
                <div class="modal fade" id="rejectModal{{ $jurnal->jurnal_id }}" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <form action="{{ route('approval-jurnal.reject', $jurnal->jurnal_id) }}" method="POST">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title">Tolak Jurnal</h5>
                                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label>Alasan Penolakan</label>
                                        <textarea name="reason" class="form-control" required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-danger">Tolak</button>
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <tr>
                    <td colspan="7" class="text-center">Tidak ada jurnal</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@stop
