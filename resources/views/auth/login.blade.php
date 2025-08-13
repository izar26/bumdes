<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | {{ optional($bumdes)->nama_bumdes ?? config('app.name') }}</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        :root {
            --primary-color: #008080;
            --secondary-color: #006666;
            --background-color: #f7fafc;
            --text-color: #4a5568;
            --card-bg: #ffffff;
            --input-border: #e2e8f0;
            --input-focus-border: #008080;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-color);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: var(--text-color);
        }

        .login-container {
            display: flex;
            width: 100%;
            max-width: 1000px;
            min-height: 600px;
            background-color: var(--card-bg);
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        /* Sisi Kiri - Branding/Info */
        .login-info {
            width: 50%;
            padding: 50px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
            background-size: cover;
            background-position: center;
            position: relative;
            /* Gambar desa/alam yang relevan */
            background-image: url('https://images.unsplash.com/photo-1500382017468-9049fed747ef?q=80&w=1932&auto=format&fit=crop');
        }

        .login-info::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0, 128, 128, 0.85), rgba(0, 102, 102, 0.9));
            z-index: 1;
        }

        .info-content {
            position: relative;
            z-index: 2;
        }

        .info-content .logo {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.2);
            margin: 0 auto 20px auto;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .info-content .logo img {
            max-width: 70%;
            max-height: 70%;
        }

        .info-content h1 {
            font-size: 24px;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .info-content p {
            font-size: 14px;
            opacity: 0.9;
        }

        /* Sisi Kanan - Form Login */
        .login-form {
            width: 50%;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-form h2 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #2d3748;
        }

        .login-form p {
            margin-bottom: 30px;
            font-size: 15px;
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px 12px 40px;
            border: 1px solid var(--input-border);
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--input-focus-border);
            box-shadow: 0 0 0 3px rgba(0, 128, 128, 0.15);
        }

        .input-group .icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background-color: var(--primary-color);
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-login:hover {
            background-color: var(--secondary-color);
        }

        .login-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            font-size: 13px;
        }

        .remember-me {
            display: flex;
            align-items: center;
        }

        .remember-me input {
            margin-right: 8px;
        }

        .invalid-feedback {
            color: #e53e3e;
            font-size: 12px;
            display: block;
            margin-top: 5px;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .login-container {
                flex-direction: column;
                max-width: 450px;
                min-height: auto;
            }
            .login-info, .login-form {
                width: 100%;
            }
            .login-info {
                padding: 40px 20px;
                min-height: 250px;
            }
        }
    </style>
</head>
<body>

    @php
        // Ambil data BUMDes
        $bumdes = \App\Models\Bungdes::first();

        // Setup URL dari AdminLTE config
        $login_url = View::getSection('login_url') ?? config('adminlte.login_url', 'login');
        if (config('adminlte.use_route_url', false)) {
            $login_url = $login_url ? route($login_url) : '';
        } else {
            $login_url = $login_url ? url($login_url) : '';
        }
    @endphp

    <div class="login-container">
        <!-- Sisi Kiri - Branding -->
        <div class="login-info">
            <div class="info-content">
                <div class="logo">
                    @if(optional($bumdes)->logo)
                        <img src="{{ asset('storage/' . $bumdes->logo) }}" alt="Logo">
                    @else
                        {{-- Icon fallback jika logo tidak ada --}}
                        <i class="fas fa-landmark fa-2x"></i>
                    @endif
                </div>
                <h1>{{ optional($bumdes)->nama_bumdes ?? 'Sistem Informasi BUMDes' }}</h1>
                <p>Mewujudkan transparansi dan akuntabilitas untuk kemajuan desa.</p>
            </div>
        </div>

        <!-- Sisi Kanan - Form Login -->
        <div class="login-form">
            <h2>Selamat Datang</h2>
            <p>Silakan masuk untuk melanjutkan ke sistem.</p>

            <form action="{{ $login_url }}" method="post">
                @csrf

                {{-- Username Input --}}
                <div class="input-group">
                    <i class="fas fa-user icon"></i>
                    <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                           value="{{ old('username') }}" placeholder="Username" required autofocus>
                </div>
                @error('username')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror

                {{-- Password Input --}}
                <div class="input-group" style="margin-top: 20px;">
                    <i class="fas fa-lock icon"></i>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                           placeholder="Password" required>
                </div>
                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
                
                {{-- Opsi Login --}}
                <div class="login-options">
                    <div class="remember-me">
                        <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label for="remember">Ingat Saya</label>
                    </div>
                    {{-- Anda bisa menambahkan link lupa password di sini jika ada --}}
                    {{-- <a href="#">Lupa Password?</a> --}}
                </div>

                {{-- Tombol Login --}}
                <div style="margin-top: 30px;">
                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i> Masuk
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
