@extends('layouts.auth')

@section('title', 'Login')
@section('heading', 'Masuk')
@section('subtitle', 'Akses workspace Anda')

@section('content')
<form method="POST" action="{{ route('login.store') }}">
    @csrf
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus>
    </div>
    <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
    </div>
    <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="remember" name="remember" value="1">
        <label class="form-check-label" for="remember">Ingat saya</label>
    </div>

    <button class="btn btn-accent w-100" type="submit">Login</button>
</form>

<div class="d-flex justify-content-between mt-3">
    <a href="{{ route('register') }}">Buat akun</a>
    <a href="{{ route('password.request') }}">Lupa password?</a>
</div>
@endsection
