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

    <div class="card mb-3">
        <div class="card-body">
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
    </div>

    {{-- Desktop: data table --}}
    <div class="card d-none d-md-block">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Nama</th><th>Email</th><th>HP</th><th>Role</th><th>Status Teknisi</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td class="fw-semibold">{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone ?: '—' }}</td>
                            <td><span class="badge text-bg-secondary">{{ $user->role->label() }}</span></td>
                            <td>
                                @if ($user->technician)
                                    <span class="badge text-bg-{{ $user->technician->status->color() }}">
                                        {{ $user->technician->status->label() }}
                                    </span>
                                @else — @endif
                            </td>
                            <td class="text-end">
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
    </div>

    {{-- Mobile: compact cards --}}
    <div class="d-md-none">
        @foreach ($users as $user)
            <div class="card mb-2">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div class="flex-grow-1" style="min-width:0">
                            <div class="fw-semibold text-truncate">{{ $user->name }}</div>
                            <div class="text-muted small text-truncate"><i class="bi bi-envelope me-1"></i>{{ $user->email }}</div>
                            @if ($user->phone)
                                <div class="text-muted small text-truncate"><i class="bi bi-telephone me-1"></i>{{ $user->phone }}</div>
                            @endif
                        </div>
                        <div class="d-flex gap-1 flex-shrink-0">
                            <a href="{{ route('users.edit', $user) }}"
                               class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                            @if ($user->role->value !== 'admin')
                                <form method="POST" action="{{ route('users.destroy', $user) }}"
                                      class="d-inline" onsubmit="return confirm('Hapus user ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            @endif
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-1 mt-2 pt-2 border-top">
                        <span class="badge text-bg-secondary">{{ $user->role->label() }}</span>
                        @if ($user->technician)
                            <span class="badge text-bg-{{ $user->technician->status->color() }}">
                                {{ $user->technician->status->label() }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if ($users->hasPages())
        <div class="mt-3 d-flex justify-content-center justify-content-md-start">
            {{ $users->links() }}
        </div>
    @endif
@endsection
