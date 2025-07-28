@extends('adminlte::page')

@section('title', 'Edit Profil BUMDes')

@section('content')
<section class="py-5 px-3 bg-white position-relative" style="overflow: hidden;">
    <div class="position-absolute top-0 end-0 bg-green-100 rounded-circle" style="width: 16rem; height: 16rem; filter: blur(100px); opacity: 0.4;"></div>
    <div class="position-absolute bottom-0 start-0 bg-green-50 rounded-circle" style="width: 20rem; height: 20rem; filter: blur(100px); opacity: 0.4;"></div>

    <div class="position-relative container" style="max-width: 1000px;">
        <div class="card shadow-sm border border-green-100">
            <div class="card-header bg-gradient bg-success text-white">
                <h2 class="card-title h5 m-0">
                    <i class="fas fa-building me-2"></i>
                    Formulir Profil BUMDes
                </h2>
            </div>

            <form action="{{ route('admin.bungdes.update') }}" method="POST" enctype="multipart/form-data" class="card-body">
                @csrf
                @method('PUT')

                <div class="row g-4">
                    <!-- Nama BUMDes -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nama_bumdes" class="form-label">
                                <i class="fas fa-building me-2 text-success"></i>
                                Nama BUMDes <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-building text-secondary"></i></span>
                                <input type="text" name="nama_bumdes" id="nama_bumdes" class="form-control" required
                                    value="{{ old('nama_bumdes', $bungdeses->nama_bumdes) }}"
                                    placeholder="Masukkan nama BUMDes">
                            </div>
                            @error('nama_bumdes')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Alamat -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="alamat" class="form-label">
                                <i class="fas fa-map-marker-alt me-2 text-success"></i>
                                Alamat <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-map-marker-alt text-secondary"></i></span>
                                <input type="text" name="alamat" id="alamat" class="form-control" required
                                    value="{{ old('alamat', $bungdeses->alamat) }}"
                                    placeholder="Masukkan alamat lengkap">
                            </div>
                            @error('alamat')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Tanggal Berdiri -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tanggal_berdiri" class="form-label">
                                <i class="fas fa-calendar-alt me-2 text-success"></i>
                                Tanggal Berdiri
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-calendar-alt text-secondary"></i></span>
                                <input type="date" name="tanggal_berdiri" id="tanggal_berdiri" class="form-control"
                                    value="{{ old('tanggal_berdiri', $bungdeses->tanggal_berdiri ? \Carbon\Carbon::parse($bungdeses->tanggal_berdiri)->format('Y-m-d') : '') }}">
                            </div>
                            @error('tanggal_berdiri')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Aset Usaha -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="aset_usaha" class="form-label">
                                <i class="fas fa-wallet me-2 text-success"></i>
                                Aset Usaha
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-wallet text-secondary"></i></span>
                                <input type="text" name="aset_usaha" id="aset_usaha" class="form-control"
                                    value="{{ old('aset_usaha', $bungdeses->aset_usaha) }}"
                                    placeholder="Contoh: Rp 500.000.000">
                            </div>
                            @error('aset_usaha')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-2 text-success"></i>
                                Email
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope text-secondary"></i></span>
                                <input type="email" name="email" id="email" class="form-control"
                                    value="{{ old('email', $bungdeses->email) }}"
                                    placeholder="email@bumdes.example">
                            </div>
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Telepon -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="telepon" class="form-label">
                                <i class="fas fa-phone me-2 text-success"></i>
                                Telepon
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone text-secondary"></i></span>
                                <input type="text" name="telepon" id="telepon" class="form-control"
                                    value="{{ old('telepon', $bungdeses->telepon) }}"
                                    placeholder="Contoh: +6281234567890">
                            </div>
                            @error('telepon')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Deskripsi -->
                    <div class="col-12">
                        <div class="form-group">
                            <label for="deskripsi" class="form-label">
                                <i class="fas fa-info-circle me-2 text-success"></i>
                                Deskripsi BUMDes
                            </label>
                            <textarea name="deskripsi" id="deskripsi" rows="5" class="form-control"
                                placeholder="Deskripsikan visi misi, unit usaha, dan kegiatan BUMDes">{{ old('deskripsi', $bungdeses->deskripsi) }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Logo -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-image me-2 text-success"></i>
                                Logo BUMDes
                            </label>
                            <div class="d-flex align-items-center gap-3">
                                <input type="file" name="logo" accept="image/*" class="form-control">
                                @if ($bungdeses->logo)
                                    <div class="flex-shrink-0">
                                        <p class="small text-muted mb-1">Logo Saat Ini:</p>
                                        <img src="{{ asset('storage/' . $bungdeses->logo) }}"
                                            class="img-thumbnail" style="width: 80px; height: 80px; object-fit: contain;"
                                            alt="Logo BUMDes">
                                    </div>
                                @endif
                            </div>
                            <small class="text-muted">Format: JPG/PNG, Maks: 2MB</small>
                            @error('logo')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Struktur Organisasi -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-sitemap me-2 text-success"></i>
                                Struktur Organisasi
                            </label>
                            <div class="d-flex align-items-center gap-3">
                                <input type="file" name="struktur_organisasi" accept="image/*" class="form-control">
                                @if ($bungdeses->struktur_organisasi)
                                    <div class="flex-shrink-0">
                                        <p class="small text-muted mb-1">Struktur Saat Ini:</p>
                                        <img src="{{ asset('storage/' . $bungdeses->struktur_organisasi) }}"
                                            class="img-thumbnail" style="width: 80px; height: 80px; object-fit: contain;"
                                            alt="Struktur Organisasi">
                                    </div>
                                @endif
                            </div>
                            <small class="text-muted">Format: JPG/PNG/PDF, Maks: 5MB</small>
                            @error('struktur_organisasi')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Tombol -->
                <div class="mt-5 d-flex flex-column flex-sm-row justify-content-end gap-3">
                    <a href="{{ route('admin.bungdes.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-success bg-gradient">
                        <i class="fas fa-save me-2"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
