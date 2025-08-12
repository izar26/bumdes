@extends('adminlte::page')

@section('title', 'Pemeliharaan Aset BUMDes')

@section('content_header')
    <h1><i class="fas fa-fw fa-tools"></i> Pemeliharaan Aset BUMDes</h1>
@stop

@section('content')
    <div class="card card-secondary card-outline">
        <div class="card-header">
            <h3 class="card-title">Manajemen Pemeliharaan Aset</h3>
            <div class="card-tools">
                <a href="#" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#pemeliharaanModal">
                    <i class="fas fa-plus mr-1"></i> Tambah Riwayat Pemeliharaan
                </a>
            </div>
        </div>
        <div class="card-body">
            <p class="text-muted">Kelola jadwal dan riwayat pemeliharaan untuk aset-aset BUMDes.</p>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="pemeliharaanTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nomor Inventaris</th>
                            <th>Nama Aset</th>
                            <th>Kondisi</th>
                            <th>Unit Usaha</th>
                            <th>Tanggal Terakhir</th>
                            <th>Tanggal Berikutnya</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($asets as $aset)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $aset->nomor_inventaris ?? '-' }}</td>
                                <td>{{ $aset->nama_aset }}</td>
                                <td><span class="badge badge-{{ $aset->kondisi == 'Baik' ? 'success' : ($aset->kondisi == 'Rusak Ringan' ? 'warning' : 'danger') }}">{{ $aset->kondisi }}</span></td>
                                <td>{{ $aset->unitUsaha->nama_unit ?? '-' }}</td>
                                <td>{{ $aset->terakhir_pemeliharaan ?? '-' }}</td>
                                <td>{{ $aset->berikutnya_pemeliharaan ?? '-' }}</td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-info" data-toggle="modal" data-target="#pemeliharaanModal" data-aset-id="{{ $aset->aset_id }}">
                                        <i class="fas fa-history"></i> Riwayat
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Tidak ada aset yang terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    {{-- Modal Tambah Riwayat Pemeliharaan --}}
    <div class="modal fade" id="pemeliharaanModal" tabindex="-1" role="dialog" aria-labelledby="pemeliharaanModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pemeliharaanModalLabel">Tambah Riwayat Pemeliharaan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="#" method="POST" id="pemeliharaanForm">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="aset_id">Aset</label>
                            <select name="aset_id" id="aset_id" class="form-control" required>
                                <option value="">-- Pilih Aset --</option>
                                @foreach($asets as $aset)
                                    <option value="{{ $aset->aset_id }}">{{ $aset->nama_aset }} ({{ $aset->nomor_inventaris }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="tanggal_pemeliharaan">Tanggal Pemeliharaan</label>
                            <input type="date" name="tanggal_pemeliharaan" id="tanggal_pemeliharaan" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="biaya">Biaya</label>
                            <input type="number" name="biaya" id="biaya" class="form-control" placeholder="Masukkan biaya pemeliharaan" required min="0">
                        </div>
                        <div class="form-group">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3" placeholder="Jelaskan pekerjaan yang dilakukan" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Simpan Riwayat</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Inisialisasi DataTable
            $('#pemeliharaanTable').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
            });

            // Otomatis memilih aset di modal jika tombol 'Riwayat' ditekan
            $('#pemeliharaanModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget); // Tombol yang memicu modal
                var asetId = button.data('aset-id'); // Ambil ID aset dari atribut data-*

                var modal = $(this);
                if (asetId) {
                    modal.find('.modal-body #aset_id').val(asetId).prop('disabled', true);
                } else {
                    modal.find('.modal-body #aset_id').prop('disabled', false);
                }
            });
        });
    </script>
@stop
