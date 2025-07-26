<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Judul dinamis dari database --}}
    <title>{{ $profil->nama_desa }} Digital</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    {{-- Plugin line-clamp untuk membatasi teks --}}
    <script src="https://cdn.tailwindcss.com/3.4.1?plugins=forms,typography,aspect-ratio,line-clamp"></script>

    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8fafc; }
        /* Gambar Latar Hero dinamis dari database */
        .hero-bg {
            background-image: linear-gradient(to bottom, rgba(0,0,0,0.6), rgba(0,0,0,0.3)), url("{{ $settings->hero_background ? asset('storage/' . $settings->hero_background) : 'https://images.unsplash.com/photo-1598192370069-b139fd55e464?q=80&w=1974&auto=format&fit=crop' }}");
            background-size: cover;
            background-position: center;
        }
    </style>
</head>
{{-- Menambahkan state 'scrolled' untuk mendeteksi scroll --}}
<body x-data="{ open: false, scrolled: false }" @scroll.window="scrolled = (window.scrollY > 50)">

    {{-- Header diubah untuk efek transparan --}}
    <header :class="{ 'bg-white/90 backdrop-blur-sm shadow-lg': scrolled, 'bg-transparent text-white': !scrolled }" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            {{-- Logo dan Nama Desa dinamis --}}
            <a href="#" class="text-2xl font-bold flex items-center">
                @if($profil->logo)
                    <img src="{{ asset('storage/' . $profil->logo) }}" alt="Logo" class="h-8 mr-2">
                @else
                    <i class="fa-solid fa-seedling mr-2"></i>
                @endif
                {{ $profil->nama_desa }}
            </a>
            {{-- Menu Desktop dengan efek hover baru --}}
            <ul class="hidden md:flex items-center space-x-8">
                <li><a href="#hero" class="pb-1 border-b-2 border-transparent hover:border-green-500 hover:text-green-500 transition-colors">Beranda</a></li>
                <li><a href="#profil" class="pb-1 border-b-2 border-transparent hover:border-green-500 hover:text-green-500 transition-colors">Profil</a></li>
                <li><a href="#potensi" class="pb-1 border-b-2 border-transparent hover:border-green-500 hover:text-green-500 transition-colors">Potensi</a></li>
                <li><a href="#berita" class="pb-1 border-b-2 border-transparent hover:border-green-500 hover:text-green-500 transition-colors">Berita</a></li>
                <li><a href="#kontak" class="bg-green-600 text-white px-4 py-2 rounded-full hover:bg-green-700 transition-transform hover:scale-105">Kontak Kami</a></li>
            </ul>
            {{-- Ikon hamburger diubah agar kontras dengan latar --}}
            <button @click="open = !open" class="md:hidden" :class="{'text-gray-700': scrolled, 'text-white': !scrolled}">
                <i class="fa-solid fa-bars fa-xl"></i>
            </button>
        </nav>
        {{-- Menu Mobile --}}
        <div x-show="open" @click.away="open = false" class="md:hidden bg-white border-t">
              <a href="#hero" class="block px-6 py-3 text-gray-600 hover:bg-gray-100">Beranda</a>
              <a href="#profil" class="block px-6 py-3 text-gray-600 hover:bg-gray-100">Profil</a>
              <a href="#potensi" class="block px-6 py-3 text-gray-600 hover:bg-gray-100">Potensi</a>
              <a href="#berita" class="block px-6 py-3 text-gray-600 hover:bg-gray-100">Berita</a>
              <a href="#kontak" class="block px-6 py-3 text-gray-600 hover:bg-gray-100">Kontak Kami</a>
        </div>
    </header>

    <section id="hero" class="hero-bg h-screen flex items-center justify-center text-white">
        <div class="text-center" data-aos="fade-up">
            {{-- Teks Hero dinamis --}}
            <h1 class="text-4xl md:text-6xl font-bold mb-4">{{ $settings->hero_headline }}</h1>
            <p class="text-lg md:text-xl mb-8 max-w-2xl mx-auto">{{ $settings->hero_tagline }}</p>
            {{-- Tombol dengan efek hover baru --}}
            <a href="#profil" class="bg-green-600 text-white px-8 py-3 rounded-full text-lg font-semibold hover:bg-green-700 transition-all duration-300 hover:shadow-lg hover:scale-105">
                Jelajahi Desa <i class="fa-solid fa-arrow-right ml-2"></i>
            </a>
        </div>
    </section>

    <main class="container mx-auto px-6 py-16 space-y-24">
        
        <section id="profil" class="grid md:grid-cols-2 gap-12 items-center">
            <div data-aos="fade-right">
                {{-- Gambar Profil dinamis --}}
                <img src="{{ $settings->profil_image ? asset('storage/' . $settings->profil_image) : 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?q=80&w=1932&auto=format&fit=crop' }}" alt="Pemandangan Desa" class="rounded-xl shadow-2xl w-full h-auto object-cover">
            </div>
            <div data-aos="fade-left" data-aos-delay="200">
                {{-- Data Profil dinamis --}}
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Profil {{ $profil->nama_desa }}</h2>
                <p class="text-gray-600 mb-4">{{ $profil->deskripsi }}</p>
                {{-- Statistik dengan visualisasi ikon --}}
                <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-6 text-center">
                    <div class="bg-green-50 p-4 rounded-lg transition-transform hover:scale-105">
                        <i class="fa-solid fa-users text-green-600 text-3xl mb-2"></i>
                        <p class="text-2xl font-bold text-gray-800">{{ number_format($profil->jumlah_penduduk) }}+</p>
                        <p class="text-gray-500">Penduduk</p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg transition-transform hover:scale-105">
                        <i class="fa-solid fa-house-user text-green-600 text-3xl mb-2"></i>
                        <p class="text-2xl font-bold text-gray-800">{{ number_format($profil->jumlah_kk) }}+</p>
                        <p class="text-gray-500">Keluarga</p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg transition-transform hover:scale-105">
                        <i class="fa-solid fa-map-marked-alt text-green-600 text-3xl mb-2"></i>
                        <p class="text-2xl font-bold text-gray-800">{{ $profil->luas_wilayah }}</p>
                        <p class="text-gray-500">Luas Wilayah</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Section dengan background berbeda agar tidak monoton --}}
        <section id="potensi" class="bg-slate-50 py-20 -mx-6 px-6 md:rounded-xl">
            <div class="container mx-auto">
                <div class="text-center mb-12" data-aos="fade-up">
                    <h2 class="text-3xl font-bold text-gray-800">Potensi Unggulan Desa</h2>
                    <p class="text-gray-600 mt-2">Inilah kekayaan yang kami miliki dan kembangkan bersama.</p>
                </div>
                <div class="grid md:grid-cols-3 gap-8">
                    {{-- Loop untuk data Potensi dinamis dengan kartu baru --}}
                    @forelse($potensis as $potensi)
                    <div class="bg-white rounded-xl shadow-md overflow-hidden group transition-all duration-300 hover:shadow-xl hover:-translate-y-2" data-aos="fade-up" data-aos-delay="{{ $loop->iteration * 100 }}">
                        <div class="overflow-hidden">
                            <img src="{{ $potensi->gambar ? asset('storage/' . $potensi->gambar) : 'https://via.placeholder.com/400x200.png?text=Gambar+Potensi' }}" class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-105" alt="{{ $potensi->judul }}">
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-semibold mb-2 text-gray-800">{{ $potensi->judul }}</h3>
                            <p class="text-gray-600 line-clamp-3">{{ $potensi->deskripsi }}</p>
                        </div>
                    </div>
                    @empty
                    <p class="col-span-3 text-center text-gray-500">Data potensi desa belum tersedia.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section id="berita">
            <div class="text-center mb-12" data-aos="fade-up">
                <h2 class="text-3xl font-bold text-gray-800">Berita & Informasi Terkini</h2>
                <p class="text-gray-600 mt-2">Ikuti perkembangan dan kegiatan terbaru di desa kami.</p>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                {{-- Loop untuk data Berita dinamis dengan kartu baru --}}
                @forelse($beritas as $berita)
<div class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-300 border-l-4 border-transparent hover:border-green-500" data-aos="fade-up" data-aos-delay="{{ $loop->iteration * 100 }}">
    
    {{-- Diubah menjadi $berita->gambar sesuai nama kolom di database --}}
    @if($berita->gambar)
    <img src="{{ asset('storage/' . $berita->gambar) }}" alt="{{ $berita->judul }}" class="w-full h-40 object-cover">
    @endif

    {{-- Class 'p-6' dipindahkan ke div pembungkus agar padding konsisten --}}
    <div class="p-6">
        <span class="text-sm text-gray-500 flex items-center mb-2">
            <i class="fa-regular fa-calendar-alt mr-2"></i>
            {{ $berita->created_at->format('d F Y') }}
        </span>
        <h4 class="font-semibold text-lg my-2"><a href="#" class="hover:text-green-600 transition-colors">{{ $berita->judul }}</a></h4>
        <p class="text-gray-600 text-sm line-clamp-3">{{ $berita->konten }}</p>
    </div>
</div>
@empty
<p class="col-span-3 text-center text-gray-500">Belum ada berita untuk saat ini.</p>
@endforelse
            </div>
        </section>

    </main>
    
    <footer id="kontak" class="bg-gray-800 text-white pt-16 pb-8">
        <div class="container mx-auto px-6">
            <div class="grid md:grid-cols-3 gap-8 mb-8">
                <div data-aos="fade-up">
                    {{-- Info Kontak dinamis --}}
                    <h4 class="text-xl font-bold mb-4">{{ $profil->nama_desa }}</h4>
                    <p class="text-gray-400">{{ $profil->alamat }}</p>
                    <p class="text-gray-400 mt-2">Email: <a href="mailto:{{ $profil->email }}" class="hover:text-green-400">{{ $profil->email }}</a></p>
                    <p class="text-gray-400">Telp: <a href="tel:{{ $profil->telepon }}" class="hover:text-green-400">{{ $profil->telepon }}</a></p>
                </div>
                <div data-aos="fade-up" data-aos-delay="100">
                    <h4 class="text-xl font-bold mb-4">Tautan Cepat</h4>
                    <ul class="space-y-2">
                        <li><a href="#profil" class="text-gray-400 hover:text-white">Profil Desa</a></li>
                        <li><a href="#potensi" class="text-gray-400 hover:text-white">Potensi Desa</a></li>
                        <li><a href="#berita" class="text-gray-400 hover:text-white">Berita Terbaru</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Layanan Publik</a></li>
                    </ul>
                </div>
                <div data-aos="fade-up" data-aos-delay="200">
                    <h4 class="text-xl font-bold mb-4">Ikuti Kami</h4>
                    <div class="flex space-x-4">
                        {{-- Loop untuk Link Media Sosial dinamis --}}
                        @foreach($socials as $social)
                        <a href="{{ $social->url }}" target="_blank" class="text-gray-400 hover:text-white text-2xl transition-transform hover:scale-110"><i class="{{ $social->icon }}"></i></a>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-6 text-center text-gray-500 text-sm">
                <p>&copy; {{ date('Y') }} {{ $profil->nama_desa }} Digital. Dibuat dengan <i class="fa-solid fa-heart text-red-500"></i> untuk kemajuan desa.</p>
            </div>
        </div>
    </footer>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true,
        });
    </script>
</body>
</html>