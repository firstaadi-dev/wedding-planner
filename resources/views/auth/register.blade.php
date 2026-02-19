@extends('layouts.auth')

@section('title', 'Register')
@section('heading', 'Daftar Akun')
@section('subtitle', 'Mulai dari plan Free (limit fitur aktif)')

@section('content')
<form method="POST" action="{{ route('register.store') }}">
    @csrf
    <div class="mb-3">
        <label class="form-label">Nama</label>
        <input type="text" name="name" value="{{ old('name') }}" class="form-control" required autofocus>
    </div>
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Konfirmasi Password</label>
        <input type="password" name="password_confirmation" class="form-control" required>
    </div>

    <button class="btn btn-accent w-100" type="submit">Daftar</button>
</form>

<div class="text-center mt-3">
    Sudah punya akun? <a href="{{ route('login') }}">Login</a>
</div>
@endsection
