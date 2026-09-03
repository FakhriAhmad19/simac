@extends('layouts.app')
@section('title', 'Layanan')

@section('actions')
    @if (auth()->user()->isAdmin())
        <a href="{{ route('services.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Layanan Baru
        </a>
    @endif
@endsection

@section('content')
    <x-page-guide title="Panduan: Layanan">
        <ul>
            <li>Halaman ini berisi daftar <strong>jenis layanan</strong> beserta harga dan estimasi durasi pengerjaan.</li>
            <li>Layanan yang terdaftar di sini akan muncul sebagai pilihan saat membuat <strong>Booking</strong>.</li>
            @if (auth()->user()->isAdmin())
                <li>Tombol <span class="badge text-bg-warning">✎</span> untuk <strong>mengedit</strong>, dan <span class="badge text-bg-danger">🗑</span> untuk <strong>menghapus</strong> layanan.</li>
                <li>Klik <strong>Layanan Baru</strong> untuk menambahkan jenis layanan.</li>
            @endif
        </ul>
    </x-page-guide>

    {{-- Desktop: data table --}}
    <div class="card d-none d-md-block">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Nama</th><th>Deskripsi</th><th class="text-end">Harga</th>
                        <th class="text-center">Durasi</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($services as $service)
                        <tr>
                            <td class="fw-semibold">{{ $service->name }}</td>
                            <td class="text-muted">{{ Str::limit($service->description, 60) ?: '—' }}</td>
                            <td class="text-end">Rp {{ number_format($service->price, 0, ',', '.') }}</td>
                            <td class="text-center">{{ $service->estimated_duration ? $service->estimated_duration.' mnt' : '—' }}</td>
                            <td class="text-end">
                                @if (auth()->user()->isAdmin())
                                    <a href="{{ route('services.edit', $service) }}"
                                       class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                    <form method="POST" action="{{ route('services.destroy', $service) }}"
                                          class="d-inline" onsubmit="return confirm('Hapus layanan ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Belum ada layanan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile: compact cards --}}
    <div class="d-md-none">
        @forelse ($services as $service)
            <div class="card mb-2">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div class="flex-grow-1" style="min-width:0">
                            <div class="fw-semibold text-truncate">{{ $service->name }}</div>
                            @if ($service->description)
                                <div class="text-muted small mt-1">{{ Str::limit($service->description, 80) }}</div>
                            @endif
                        </div>
                        @if (auth()->user()->isAdmin())
                            <div class="d-flex gap-1 flex-shrink-0">
                                <a href="{{ route('services.edit', $service) }}"
                                   class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('services.destroy', $service) }}"
                                      class="d-inline" onsubmit="return confirm('Hapus layanan ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                        <span class="fw-semibold">Rp {{ number_format($service->price, 0, ',', '.') }}</span>
                        <span class="text-muted small">
                            <i class="bi bi-clock me-1"></i>{{ $service->estimated_duration ? $service->estimated_duration.' mnt' : '—' }}
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center text-muted py-4">Belum ada layanan.</div>
            </div>
        @endforelse
    </div>

    @if ($services->hasPages())
        <div class="mt-3 d-flex justify-content-center justify-content-md-start">
            {{ $services->links() }}
        </div>
    @endif
@endsection
