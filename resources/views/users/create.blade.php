@extends('layouts.app')
@section('title', 'User Baru')

@section('content')
    <x-page-guide title="Panduan: Tambah User">
        <ul>
            <li>Isi data akun dan pilih <strong>peran</strong>: Owner atau Teknisi.</li>
            <li>Bila memilih <strong>Teknisi</strong>, profil teknisi akan otomatis dibuat dan bisa diatur di menu Teknisi.</li>
            <li><strong>Password</strong> minimal sesuai ketentuan dan harus diketik ulang untuk konfirmasi.</li>
        </ul>
    </x-page-guide>

    <div class="card"><div class="card-body">
        <div class="col-lg-7">
            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="name">Nama <span class="text-danger">*</span></label>
                    <input id="name" name="name" value="{{ old('name') }}"
                           class="form-control @error('name') is-invalid @enderror" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="row g-3">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="email">Email <span class="text-danger">*</span></label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}"
                               class="form-control @error('email') is-invalid @enderror" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="phone">Nomor HP</label>
                        <input id="phone" name="phone" value="{{ old('phone') }}" class="form-control">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="role">Role <span class="text-danger">*</span></label>
                    <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required>
                        <option value="owner" @selected(old('role') === 'owner')>Owner/Manager</option>
                        <option value="technician" @selected(old('role', 'technician') === 'technician')>Teknisi</option>
                    </select>
                    @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3" id="specialization-group">
                    <label class="form-label" for="specialization">Spesialisasi (teknisi)</label>
                    <input id="specialization" name="specialization" value="{{ old('specialization') }}"
                           class="form-control" placeholder="mis. Servis rutin, bongkar-pasang">
                </div>
                <div class="row g-3">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="password">Password <span class="text-danger">*</span></label>
                        <input id="password" name="password" type="password"
                               class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="password_confirmation">Konfirmasi Password <span class="text-danger">*</span></label>
                        <input id="password_confirmation" name="password_confirmation" type="password"
                               class="form-control" required>
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

@push('scripts')
<script>
    const role = document.getElementById('role');
    const specGroup = document.getElementById('specialization-group');
    const toggleSpec = () => specGroup.style.display = role.value === 'technician' ? '' : 'none';
    role.addEventListener('change', toggleSpec);
    toggleSpec();
</script>
@endpush
