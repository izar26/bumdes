{{-- Pastikan file ini menerima variabel $angsuran --}}
<div class="modal fade" id="bayarModal{{ $angsuran->angsuran_id }}" tabindex="-1" role="dialog" aria-labelledby="bayarModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title" id="bayarModalLabel">Catat Pembayaran Angsuran Ke-**{{ $angsuran->angsuran_ke }}**</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>

            <form action="{{ route('simpanan.angsuran.bayar.store', $angsuran->angsuran_id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Anggota: **{{ $angsuran->pinjaman->anggota->nama_lengkap ?? 'N/A' }}**</p>
                    <p>Jumlah Wajib Bayar: **Rp {{ number_format($angsuran->jumlah_bayar) }}**</p>
                    <p>Jatuh Tempo: {{ $angsuran->tanggal_jatuh_tempo->format('d M Y') }}</p>
                    <hr>

                    <div class="form-group">
                        <label for="tanggal_bayar">Tanggal Bayar</label>
                        <input type="date" name="tanggal_bayar" class="form-control" value="{{ now()->toDateString() }}" required>
                    </div>

                    <div class="form-group">
                        <label for="keterangan">Keterangan (Opsional)</label>
                        <textarea name="keterangan" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Konfirmasi Bayar</button>
                </div>
            </form>
        </div>
    </div>
</div>
