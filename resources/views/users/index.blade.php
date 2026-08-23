@extends('layouts.app')
@section('title', 'Manajemen User')

@section('actions')
    <a href="{{ route('users.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> User Baru
    </a>
@endsection

@section('content')
    <x-page-guide title="Panduan: Manajemen User">
        <ul>
            <li>Gunakan <strong>kolom pencarian</strong> untuk mencari user berdasarkan nama, email, atau nomor HP.</li>
            <li>Tombol <span class="badge text-bg-warning">✎</span> untuk <strong>mengedit</strong> user, dan <span class="badge text-bg-danger">🗑</span> untuk <strong>menghapus</strong>.</li>
            <li>Akun <strong>Admin tidak dapat dihapus</strong> demi keamanan sistem.</li>
            <li>Klik <strong>User Baru</strong> untuk menambahkan akun Owner atau Teknisi.</li>
        </ul>
    </x-page-guide>

    <div class="card">
        <div class="card-header bg-white">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="search" name="search" value="{{ $search }}" class="form-control"
                               placeholder="Cari nama, email, atau no. HP...">
                    </div>
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary"><i class="bi bi-search me-1"></i> Cari</button>
                </div>
                @if ($search !== '')
                    <div class="col-auto"><a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Reset</a></div>
                @endif
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 table-mobile-card">
                <thead class="table-light">
                    <tr><th>Nama</th><th>Email</th><th>HP</th><th>Role</th><th>Status Teknisi</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td class="fw-semibold" data-label="Nama">{{ $user->name }}</td>
                            <td data-label="Email">{{ $user->email }}</td>
                            <td data-label="HP">{{ $user->phone ?: '—' }}</td>
                            <td data-label="Role"><span class="badge text-bg-secondary">{{ $user->role->label() }}</span></td>
                            <td data-label="Status Teknisi">
                                @if ($user->technician)
                                    <span class="badge text-bg-{{ $user->technician->status->color() }}">
                                        {{ $user->technician->status->label() }}
                                    </span>
                                @else — @endif
                            </td>
                            <td class="text-end cell-actions">
                                <a href="{{ route('users.edit', $user) }}"
                                   class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                @if ($user->role->value !== 'admin')
                                    <form method="POST" action="{{ route('users.destroy', $user) }}"
                                          class="d-inline" onsubmit="return confirm('Hapus user ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $users->links() }}</div>
    </div>
@endsection
