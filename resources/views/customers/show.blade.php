@extends('layouts.app')
@section('title', 'Detail Customer')

@section('actions')
    @if (auth()->user()->isAdmin())
        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-warning">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        <a href="{{ route('bookings.create', ['customer_id' => $customer->id]) }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Booking
        </a>
    @endif
@endsection

@section('content')
    <x-page-guide title="Panduan: Detail Customer">
        <ul>
            <li>Panel kiri menampilkan <strong>data kontak</strong> dan daftar <strong>Unit AC</strong> milik customer.</li>
            @if (auth()->user()->isAdmin())
                <li>Gunakan <strong>+ Tambah</strong> untuk mendaftarkan unit AC, serta ikon ✎/🗑 untuk mengedit atau menghapus unit.</li>
                <li>Klik <strong>Booking</strong> untuk membuat pesanan servis langsung untuk customer ini.</li>
            @endif
            <li>Panel <strong>Riwayat Servis</strong> menampilkan seluruh booking customer — klik <strong>Detail</strong> untuk melihatnya.</li>
        </ul>
    </x-page-guide>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card mb-3"><div class="card-body">
                <h5 class="mb-1">{{ $customer->name }}</h5>
                <div class="text-muted small mb-3">Ditambahkan oleh {{ $customer->creator->name ?? '—' }}</div>
                <div class="mb-2"><i class="bi bi-telephone me-2 text-primary"></i>{{ $customer->phone }}</div>
                <div class="mb-2"><i class="bi bi-geo-alt me-2 text-primary"></i>{{ $customer->address ?: '—' }}</div>
            </div></div>

            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <strong>Unit AC</strong>
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('ac-units.create', $customer) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus"></i> Tambah
                        </a>
                    @endif
                </div>
                <ul class="list-group list-group-flush">
                    @forelse ($customer->acUnits as $unit)
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-semibold">{{ $unit->brand ?: 'Unit' }} {{ $unit->type }}</div>
                                <div class="small text-muted">
                                    {{ $unit->capacity_pk ? $unit->capacity_pk.' PK' : '' }}
                                    {{ $unit->location_note ? '· '.$unit->location_note : '' }}
                                </div>
                            </div>
                            @if (auth()->user()->isAdmin())
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('ac-units.edit', $unit) }}" class="btn btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('ac-units.destroy', $unit) }}"
                                          onsubmit="return confirm('Hapus unit AC ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            @endif
                        </li>
                    @empty
                        <li class="list-group-item text-muted text-center">Belum ada unit AC.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white"><strong>Riwayat Servis</strong></div>
                {{-- Desktop: data table --}}
                <div class="table-responsive d-none d-md-block">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Tanggal</th><th>Layanan</th><th>Teknisi</th><th>Status</th><th></th></tr>
                        </thead>
                        <tbody>
                            @forelse ($customer->bookings as $booking)
                                <tr>
                                    <td>{{ $booking->scheduled_at->format('d M Y') }}</td>
                                    <td>{{ $booking->service->name }}</td>
                                    <td>{{ $booking->technician?->user->name ?? '—' }}</td>
                                    <td><x-status-badge :status="$booking->status" /></td>
                                    <td class="text-end">
                                        <a href="{{ route('bookings.show', $booking) }}"
                                           class="btn btn-sm btn-outline-primary">Detail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada riwayat servis.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Mobile: compact rows --}}
                <div class="d-md-none">
                    @forelse ($customer->bookings as $booking)
                        <div class="p-3 border-top">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div class="flex-grow-1" style="min-width:0">
                                    <div class="fw-semibold text-truncate">{{ $booking->service->name }}</div>
                                    <div class="text-muted small"><i class="bi bi-calendar-event me-1"></i>{{ $booking->scheduled_at->format('d M Y') }}</div>
                                    <div class="text-muted small text-truncate"><i class="bi bi-person me-1"></i>{{ $booking->technician?->user->name ?? 'Belum ditugaskan' }}</div>
                                </div>
                                <x-status-badge :status="$booking->status" />
                            </div>
                            <div class="mt-2 text-end">
                                <a href="{{ route('bookings.show', $booking) }}"
                                   class="btn btn-sm btn-outline-primary">Detail</a>
                            </div>
                        </div>
                    @empty
                        <div class="p-3 text-center text-muted border-top">Belum ada riwayat servis.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
