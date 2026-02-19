@extends('layouts.auth')

@section('title', 'Verifikasi Email')
@section('heading', 'Verifikasi Email')
@section('subtitle', 'Klik link verifikasi yang kami kirim ke email Anda')

@section('content')
<p class="mb-3">Email perlu diverifikasi sebelum menggunakan aplikasi.</p>

<form method="POST" action="{{ route('verification.send') }}">
    @csrf
    <button type="submit" class="btn btn-accent w-100">Kirim Ulang Email Verifikasi</button>
</form>

<form method="POST" action="{{ route('logout') }}" class="mt-3">
    @csrf
    <button type="submit" class="btn btn-outline-secondary w-100">Logout</button>
</form>
@endsection
