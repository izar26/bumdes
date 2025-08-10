@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('adminlte_css_pre')
    <link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <style>
        /* Background gradient untuk kesan premium */
        body.login-page {
            background: linear-gradient(135deg, #f4f6f9 0%, #e0e5ec 100%);
        }

        /* Card styling */
        .login-card {
            border-radius: .75rem;
            box-shadow: 0 .5rem 1.25rem rgba(0,0,0,0.08);
            overflow: hidden;
            background: #fff;
        }

        /* Header login */
        .login-logo {
            font-weight: 700;
            font-size: 25.6px;
            letter-spacing: .0313rem;
            color: #2c3e50;
        }

        /* Input field styling */
        .form-control {
            border-radius: .5rem;
            border: .0625rem solid #dcdfe3;
            padding: 10.4px 14.4px;
            font-size: 15.2px;
        }

        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 3.2px rgba(78,115,223,0.25);
        }

        /* Button styling */
        .btn-primary {
            border-radius: .5rem;
            font-weight: 500;
            letter-spacing: .0187rem;
            transition: all 0.2s ease-in-out;
        }
        .btn-primary:hover {
            background-color: #3751c0;
        }

        /* Footer links */
        .auth-footer-links a {
            color: #4e73df;
            font-weight: 500;
            text-decoration: none;
        }
        .auth-footer-links a:hover {
            text-decoration: underline;
        }
    </style>
@stop

@php( $login_url = View::getSection('login_url') ?? config('adminlte.login_url', 'login') )
@php( $register_url = View::getSection('register_url') ?? config('adminlte.register_url', 'register') )

@if (config('adminlte.use_route_url', false))
    @php( $login_url = $login_url ? route($login_url) : '' )
    @php( $register_url = $register_url ? route($register_url) : '' )
@else
    @php( $login_url = $login_url ? url($login_url) : '' )
    @php( $register_url = $register_url ? url($register_url) : '' )
@endif

@section('auth_header')
    <div class="login-logo">
        Login Untuk Masuk Ke Sistem
    </div>


@stop

@section('auth_body')
    <form action="{{ $login_url }}" method="post" class="login-card">
        @csrf

        {{-- Username --}}
        <div class="input-group mb-3">
            <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                   value="{{ old('username') }}" placeholder="Username" autofocus>

            <div class="input-group-append">
                <div class="input-group-text bg-white">
                    <span class="fas fa-user text-secondary"></span>
                </div>
            </div>

            @error('username')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        {{-- Password --}}
        <div class="input-group mb-4">
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                   placeholder="{{ __('adminlte::adminlte.password') }}">

            <div class="input-group-append">
                <div class="input-group-text bg-white">
                    <span class="fas fa-lock text-secondary"></span>
                </div>
            </div>

            @error('password')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        {{-- Remember me + Login --}}
        <div class="row align-items-center">
            <div class="col-7">
                <div class="icheck-primary">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember">{{ __('adminlte::adminlte.remember_me') }}</label>
                </div>
            </div>

            <div class="col-5">
                <button type="submit" class="btn btn-primary btn-block">
                    <span class="fas fa-sign-in-alt"></span> {{ __('adminlte::adminlte.sign_in') }}
                </button>
            </div>
        </div>
    </form>
@stop
