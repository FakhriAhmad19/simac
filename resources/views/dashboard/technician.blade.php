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
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-mobile-card">
                    <thead class="table-light">
                        <tr><th>Jadwal</th><th>Customer</th><th>Layanan</th><th>Unit AC</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($activeBookings as $booking)
                            <tr>
                                <td data-label="Jadwal">{{ $booking->scheduled_at->format('d M Y H:i') }}</td>
                                <td data-label="Customer">{{ $booking->customer->name }}</td>
                                <td data-label="Layanan">{{ $booking->service->name }}</td>
                                <td data-label="Unit AC">{{ $booking->acUnit?->label() ?? '—' }}</td>
                                <td data-label="Status"><x-status-badge :status="$booking->status" /></td>
                                <td class="text-end cell-actions">
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
        </div>

        <div class="card">
            <div class="card-header bg-white"><strong>Riwayat Tugas Selesai</strong></div>
            <div class="table-responsive">
                <table class="table align-middle mb-0 table-mobile-card">
                    <thead class="table-light">
                        <tr><th>Jadwal</th><th>Customer</th><th>Layanan</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($completedBookings as $booking)
                            <tr>
                                <td data-label="Jadwal">{{ $booking->scheduled_at->format('d M Y') }}</td>
                                <td data-label="Customer">{{ $booking->customer->name }}</td>
                                <td data-label="Layanan">{{ $booking->service->name }}</td>
                                <td class="text-end cell-actions">
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
        </div>
    @endif
@endsection
