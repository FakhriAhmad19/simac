@extends('layouts.guest')
@section('title', 'Reset Password')

@section('content')
    <h2 class="h5 mb-3 text-center">Reset Password</h2>
    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $email) }}"
                   class="form-control @error('email') is-invalid @enderror" required autofocus>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">Password Baru</label>
            <input id="password" name="password" type="password"
                   class="form-control @error('password') is-invalid @enderror" required>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="password_confirmation">Konfirmasi Password</label>
            <input id="password_confirmation" name="password_confirmation" type="password"
                   class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Simpan Password Baru</button>
    </form>
@endsection
