@extends('adminlte::page')

@section('title', 'Pengaturan Halaman')

@section('content_header')
    <h1 class="m-0 text-dark">Pengaturan Halaman Depan</h1>
@stop

@section('content')
    <form action="{{ route('admin.homepage_setting.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        @if ($message = Session::get('success'))
                            <div class="alert alert-success alert-dismissible">
                                <i class="icon fas fa-check"></i> {{ $message }}
                            </div>
                        @endif

                        {{-- Hero Section --}}
                        <h4>Hero Section</h4>
                        <div class="form-group">
                            <label for="hero_headline">Judul Utama</label>
                            <input type="text" name="hero_headline" class="form-control" value="{{ old('hero_headline', $settings->hero_headline) }}">
                        </div>
                        <div class="form-group">
                            <label for="hero_tagline">Sub-judul / Tagline</label>
                            <textarea name="hero_tagline" class="form-control" rows="3">{{ old('hero_tagline', $settings->hero_tagline) }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="hero_background">Gambar Latar Hero (Kosongkan jika tidak ganti)</label>
                            <input type="file" name="hero_background" class="form-control-file">
                             @if($settings->hero_background)
                                <div class="mt-2"><small>Gambar saat ini:</small><br><img src="{{ asset('storage/' . $settings->hero_background) }}" style="max-height: 100px;"></div>
                            @endif
                        </div>
                        <hr>
                        {{-- Profil Section --}}
                        <h4>Profil Section</h4>
                         <div class="form-group">
                            <label for="profil_image">Gambar Samping Profil (Kosongkan jika tidak ganti)</label>
                            <input type="file" name="profil_image" class="form-control-file">
                             @if($settings->profil_image)
                                <div class="mt-2"><small>Gambar saat ini:</small><br><img src="{{ asset('storage/' . $settings->profil_image) }}" style="max-height: 100px;"></div>
                            @endif
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@stop