@extends('layouts.auth')

@section('title', 'Atur Ulang Password')
@section('heading', 'Atur Ulang Password')
@section('subtitle', 'Masukkan password baru')

@section('content')
<form method="POST" action="{{ route('password.update') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $request->route('token') }}">

    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" value="{{ old('email', $request->email) }}" class="form-control" required autofocus>
    </div>

    <div class="mb-3">
        <label class="form-label">Password Baru</label>
        <input type="password" name="password" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Konfirmasi Password Baru</label>
        <input type="password" name="password_confirmation" class="form-control" required>
    </div>

    <button class="btn btn-accent w-100" type="submit">Simpan Password Baru</button>
</form>
@endsection
