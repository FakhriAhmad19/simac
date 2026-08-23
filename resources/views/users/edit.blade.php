@extends('layouts.app')
@section('title', 'Edit User')

@section('content')
    <x-page-guide title="Panduan: Edit User">
        <ul>
            <li>Perbarui data user lalu klik <strong>Simpan</strong>.</li>
            <li><strong>Kosongkan kolom password</strong> jika tidak ingin mengubah kata sandi user.</li>
        </ul>
    </x-page-guide>

    <div class="card"><div class="card-body">
        <div class="col-lg-7">
            <form method="POST" action="{{ route('users.update', $user) }}">
                @method('PUT')
                @csrf
                <div class="mb-3">
                    <span class="badge text-bg-secondary">{{ $user->role->label() }}</span>
                    <span class="text-muted small ms-1">Role tidak dapat diubah.</span>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="name">Nama <span class="text-danger">*</span></label>
                    <input id="name" name="name" value="{{ old('name', $user->name) }}"
                           class="form-control @error('name') is-invalid @enderror" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="row g-3">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="email">Email <span class="text-danger">*</span></label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}"
                               class="form-control @error('email') is-invalid @enderror" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="phone">Nomor HP</label>
                        <input id="phone" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control">
                    </div>
                </div>
                @if ($user->role->value === 'technician')
                    <div class="mb-3">
                        <label class="form-label" for="specialization">Spesialisasi</label>
                        <input id="specialization" name="specialization"
                               value="{{ old('specialization', $user->technician->specialization ?? '') }}"
                               class="form-control">
                    </div>
                @endif
                <div class="row g-3">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="password">Password Baru</label>
                        <input id="password" name="password" type="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Kosongkan bila tidak diubah">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="password_confirmation">Konfirmasi Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password"
                               class="form-control">
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary">Simpan</button>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div></div>
@endsection
