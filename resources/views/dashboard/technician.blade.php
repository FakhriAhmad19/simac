@extends('layouts.app')
@section('title', 'Dashboard Teknisi')

@section('content')
    <x-page-guide title="Panduan: Dashboard Teknisi">
        <ul>
            <li><strong>Status Anda</strong> menentukan apakah Anda bisa ditugaskan ke booking baru.</li>
            <li>Daftar <strong>tugas</strong> di bawah adalah booking yang ditugaskan kepada Anda.</li>
            <li>Klik <strong>Kerjakan</strong> untuk mulai menangani, lalu perbarui status hingga <strong>Completed</strong>.</li>
        </ul>
    </x-page-guide>

    @if (! $technician)
        <div class="alert alert-warning">
            Profil teknisi Anda belum lengkap. Hubungi Admin.
        </div>
    @else
        <div class="d-flex align-items-center gap-2 mb-4">
            <span class="text-muted">Status Anda:</span>
            <span class="badge text-bg-{{ $technician->status->color() }} fs-6">
                {{ $technician->status->label() }}
            </span>
            @if ($technician->specialization)
                <span class="text-muted small">· {{ $technician->specialization }}</span>
            @endif
        </div>

        <div class="card mb-4">
            <div class="card-header bg-white"><strong>Tugas Aktif ({{ $activeBookings->count() }})</strong></div>
            {{-- Desktop: data table --}}
            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Jadwal</th><th>Customer</th><th>Layanan</th><th>Unit AC</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($activeBookings as $booking)
                            <tr>
                                <td>{{ $booking->scheduled_at->format('d M Y H:i') }}</td>
                                <td>{{ $booking->customer->name }}</td>
                                <td>{{ $booking->service->name }}</td>
                                <td>{{ $booking->acUnit?->label() ?? '—' }}</td>
                                <td><x-status-badge :status="$booking->status" /></td>
                                <td class="text-end">
                                    <a href="{{ route('bookings.show', $booking) }}"
                                       class="btn btn-sm btn-primary">Kerjakan</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">
                                Tidak ada tugas aktif. 🎉
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile: compact rows --}}
            <div class="d-md-none">
                @forelse ($activeBookings as $booking)
                    <div class="p-3 border-top">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="flex-grow-1" style="min-width:0">
                                <div class="fw-semibold text-truncate">{{ $booking->customer->name }}</div>
                                <div class="text-muted small text-truncate">{{ $booking->service->name }}</div>
                            </div>
                            <x-status-badge :status="$booking->status" />
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2 gap-2">
                            <div class="small text-muted" style="min-width:0">
                                <div class="text-truncate"><i class="bi bi-calendar-event me-1"></i>{{ $booking->scheduled_at->format('d M Y H:i') }}</div>
                                <div class="text-truncate"><i class="bi bi-snow2 me-1"></i>{{ $booking->acUnit?->label() ?? '—' }}</div>
                            </div>
                            <a href="{{ route('bookings.show', $booking) }}"
                               class="btn btn-sm btn-primary flex-shrink-0">Kerjakan</a>
                        </div>
                    </div>
                @empty
                    <div class="p-3 text-center text-muted border-top">Tidak ada tugas aktif. 🎉</div>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white"><strong>Riwayat Tugas Selesai</strong></div>
            {{-- Desktop: data table --}}
            <div class="table-responsive d-none d-md-block">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Jadwal</th><th>Customer</th><th>Layanan</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($completedBookings as $booking)
                            <tr>
                                <td>{{ $booking->scheduled_at->format('d M Y') }}</td>
                                <td>{{ $booking->customer->name }}</td>
                                <td>{{ $booking->service->name }}</td>
                                <td class="text-end">
                                    <a href="{{ route('bookings.show', $booking) }}"
                                       class="btn btn-sm btn-outline-secondary">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">Belum ada riwayat.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile: compact rows --}}
            <div class="d-md-none">
                @forelse ($completedBookings as $booking)
                    <div class="d-flex justify-content-between align-items-center gap-2 p-3 border-top">
                        <div class="flex-grow-1" style="min-width:0">
                            <div class="fw-semibold text-truncate">{{ $booking->customer->name }}</div>
                            <div class="text-muted small text-truncate">{{ $booking->service->name }}</div>
                            <div class="text-muted small"><i class="bi bi-calendar-event me-1"></i>{{ $booking->scheduled_at->format('d M Y') }}</div>
                        </div>
                        <a href="{{ route('bookings.show', $booking) }}"
                           class="btn btn-sm btn-outline-secondary flex-shrink-0">Detail</a>
                    </div>
                @empty
                    <div class="p-3 text-center text-muted border-top">Belum ada riwayat.</div>
                @endforelse
            </div>
        </div>
    @endif
@endsection
