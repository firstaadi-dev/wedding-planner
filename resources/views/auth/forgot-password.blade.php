@extends('layouts.auth')

@section('title', 'Reset Password')
@section('heading', 'Lupa Password')
@section('subtitle', 'Kirim link reset ke email Anda')

@section('content')
<form method="POST" action="{{ route('password.email') }}">
    @csrf
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus>
    </div>

    <button class="btn btn-accent w-100" type="submit">Kirim Link Reset</button>
</form>

<div class="text-center mt-3">
    <a href="{{ route('login') }}">Kembali ke login</a>
</div>
@endsection
