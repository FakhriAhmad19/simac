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

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 table-mobile-card">
                <thead class="table-light">
                    <tr><th>Nama</th><th>Deskripsi</th><th class="text-end">Harga</th>
                        <th class="text-center">Durasi</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($services as $service)
                        <tr>
                            <td class="fw-semibold" data-label="Nama">{{ $service->name }}</td>
                            <td class="text-muted" data-label="Deskripsi">{{ Str::limit($service->description, 60) ?: '—' }}</td>
                            <td class="text-end" data-label="Harga">Rp {{ number_format($service->price, 0, ',', '.') }}</td>
                            <td class="text-center" data-label="Durasi">{{ $service->estimated_duration ? $service->estimated_duration.' mnt' : '—' }}</td>
                            <td class="text-end cell-actions">
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
        <div class="card-footer bg-white">{{ $services->links() }}</div>
    </div>
@endsection
