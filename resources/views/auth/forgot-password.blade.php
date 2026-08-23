@extends('layouts.guest')
@section('title', 'Lupa Password')

@section('content')
    <h2 class="h5 mb-2 text-center">Lupa Password</h2>
    <p class="text-muted small text-center mb-3">
        Masukkan email Anda, kami akan mengirimkan tautan reset password.
    </p>
    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}"
                   class="form-control @error('email') is-invalid @enderror" required autofocus>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn btn-primary w-100">Kirim Tautan Reset</button>
        <a href="{{ route('login') }}" class="btn btn-link w-100 mt-2">Kembali ke halaman masuk</a>
    </form>
@endsection
