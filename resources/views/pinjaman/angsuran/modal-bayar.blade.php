{{-- Pastikan variabel $angsuran sudah di-pass ke partial ini --}}
<div class="modal fade" id="bayarModal{{ $angsuran->angsuran_id }}" tabindex="-1" role="dialog" aria-labelledby="bayarModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title" id="bayarModalLabel">Bayar Angsuran Ke-{{ $angsuran->angsuran_ke }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            {{-- FORM SUBMIT --}}
            {{-- Menggunakan route POST yang benar: simpanan.angsuran.bayar.store --}}
            <form action="{{ route('simpanan.angsuran.bayar.store', $angsuran->angsuran_id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p><strong>No Pinjaman:</strong> <span class="text-primary">{{ $angsuran->pinjaman->no_pinjaman }}</span></p>
                    <p><strong>Anggota:</strong> {{ $angsuran->pinjaman->anggota->nama_lengkap ?? 'N/A' }}</p>
                    <hr>

                    {{-- 1. INPUT NOMINAL BAYAR --}}
                    <div class="form-group">
                        <label for="nominal_bayar">Nominal Yang Dibayarkan (Rp)</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rp</span>
                            </div>
                            {{-- Default value diisi dengan jumlah tagihan seharusnya --}}
                            <input type="number" name="nominal_bayar" class="form-control text-bold"
                                   value="{{ $angsuran->jumlah_bayar }}" required>
                        </div>
                        <small class="text-muted">Tagihan seharusnya: Rp {{ number_format($angsuran->jumlah_bayar, 0, ',', '.') }}</small>
                    </div>

                    {{-- 2. INPUT TANGGAL --}}
                    <div class="form-group">
                        <label for="tanggal_bayar">Tanggal Pembayaran</label>
                        <input type="date" name="tanggal_bayar" class="form-control"
                               value="{{ now()->toDateString() }}" required>
                    </div>

                    {{-- 3. INPUT KETERANGAN --}}
                    <div class="form-group">
                        <label for="keterangan">Keterangan (Opsional)</label>
                        <textarea name="keterangan" class="form-control" rows="2" placeholder="Contoh: Tunai / Transfer">{{ old('keterangan') }}</textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Simpan Pembayaran</button>
                </div>
            </form>
        </div>
    </div>
</div>
