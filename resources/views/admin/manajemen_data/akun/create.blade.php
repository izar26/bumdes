@extends('adminlte::page')

@section('title', 'Tambah Akun Keuangan Baru')

@section('content_header')
    <h1>Tambah Akun Keuangan Baru</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Form Tambah Akun</h3>
        </div>
        <form action="{{ route('admin.akun.store') }}" method="POST">
            @csrf
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <h5><i class="icon fas fa-ban"></i> Error!</h5>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <div class="form-group">
                    <label for="kode_akun">Kode Akun</label>
                    <input type="text" name="kode_akun" class="form-control @error('kode_akun') is-invalid @enderror" id="kode_akun" placeholder="Contoh: 1101, 2000" value="{{ old('kode_akun') }}" required>
                    @error('kode_akun')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="nama_akun">Nama Akun</label>
                    <input type="text" name="nama_akun" class="form-control @error('nama_akun') is-invalid @enderror" id="nama_akun" placeholder="Contoh: Kas, Piutang Usaha" value="{{ old('nama_akun') }}" required>
                    @error('nama_akun')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="tipe_akun">Tipe Akun</label>
                    <select name="tipe_akun" id="tipe_akun" class="form-control @error('tipe_akun') is-invalid @enderror" required>
                        <option value="">-- Pilih Tipe Akun --</option>
                        @foreach ($tipeAkunOptions as $value => $label)
                            <option value="{{ $value }}" {{ old('tipe_akun') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('tipe_akun')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input type="hidden" name="is_header" value="0"> {{-- Hidden field to ensure 0 is sent if checkbox is unchecked --}}
                        <input type="checkbox" name="is_header" class="form-check-input @error('is_header') is-invalid @enderror" id="is_header" value="1" {{ old('is_header') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_header">Ini adalah Akun Header (Kelompok)</label>
                    </div>
                    @error('is_header')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="parent_id">Akun Induk (Parent)</label>
                    <select name="parent_id" id="parent_id" class="form-control @error('parent_id') is-invalid @enderror">
                        <option value="">-- Tidak Ada Akun Induk --</option>
                        @foreach ($parentAkuns as $parentAkun)
                            <option value="{{ $parentAkun->akun_id }}" {{ old('parent_id') == $parentAkun->akun_id ? 'selected' : '' }}>
                                {{ $parentAkun->kode_akun }} - {{ $parentAkun->nama_akun }}
                            </option>
                        @endforeach
                    </select>
                    @error('parent_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Simpan Akun</button>
            </div>
        </form>
    </div>
@stop
