@extends('layouts.app')
@section('title', 'Dashboard Admin')

@section('actions')
    <a href="{{ route('bookings.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Booking Baru
    </a>
@endsection

@section('content')
    @php
        use App\Enums\BookingStatus;
    @endphp

    <x-page-guide title="Panduan: Dashboard">
        <ul>
            <li>Kartu ringkasan di atas menampilkan <strong>statistik utama</strong> operasional (booking, customer, dll).</li>
            <li>Tabel di bawah menampilkan <strong>booking terbaru</strong> — klik <strong>Detail</strong> untuk menindaklanjuti.</li>
            <li>Gunakan menu di <strong>sidebar kiri</strong> untuk berpindah ke Booking, Customer, Layanan, Teknisi, atau User.</li>
        </ul>
    </x-page-guide>
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100"><div class="card-body">
                <div class="text-muted small">Menunggu (Pending)</div>
                <div class="value">{{ $statusCounts[BookingStatus::Pending->value] ?? 0 }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100"><div class="card-body">
                <div class="text-muted small">Sedang Berjalan</div>
                <div class="value">
                    {{ ($statusCounts[BookingStatus::Assigned->value] ?? 0)
                        + ($statusCounts[BookingStatus::OnTheWay->value] ?? 0)
                        + ($statusCounts[BookingStatus::InProgress->value] ?? 0) }}
                </div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100"><div class="card-body">
                <div class="text-muted small">Teknisi Available</div>
                <div class="value">{{ $availableTechnicians }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100"><div class="card-body">
                <div class="text-muted small">Total Customer</div>
                <div class="value">{{ $totalCustomers }}</div>
            </div></div>
        </div>
    </div>

    {{-- Pengingat servis berkala --}}
    <div class="card mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <strong><i class="bi bi-bell me-1 text-warning"></i> Unit AC Perlu Servis</strong>
            @if ($dueUnitsCount > 0)
                <span class="badge text-bg-warning">{{ $dueUnitsCount }} unit</span>
            @endif
        </div>
        <div class="card-body pb-2">
            <p class="small text-muted mb-3">
                Unit dengan servis terakhir lebih dari <strong>{{ $serviceIntervalDays }} hari</strong> lalu —
                saatnya ditawari servis rutin.
            </p>
            @forelse ($dueUnits as $unit)
                @php
                    $last = \Illuminate\Support\Carbon::parse($unit->last_service_at);
                    $waMsg = "Halo {$unit->customer->name}, unit AC Anda ({$unit->label()}) terakhir diservis "
                        .$last->translatedFormat('d M Y').". Sudah lebih dari {$serviceIntervalDays} hari — "
                        ."waktunya servis rutin agar AC tetap awet & dingin. Mau kami jadwalkan? — SIMAC";
                @endphp
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 border-top py-2">
                    <div class="me-auto">
                        <div class="fw-semibold">{{ $unit->customer->name }}
                            <span class="text-muted fw-normal">· {{ $unit->label() }}</span>
                        </div>
                        <div class="small text-muted">
                            Servis terakhir {{ $last->translatedFormat('d M Y') }}
                            ({{ $last->diffForHumans(['parts' => 1]) }})
                        </div>
                    </div>
                    <div class="d-flex gap-1">
                        @if ($unit->customer->phone)
                            <a href="{{ $unit->customer->waUrl($waMsg) }}" target="_blank" rel="noopener"
                               class="btn btn-sm btn-success"><i class="bi bi-whatsapp"></i><span class="d-none d-sm-inline ms-1">Ingatkan</span></a>
                        @endif
                        <a href="{{ route('bookings.create', ['customer_id' => $unit->customer_id, 'ac_unit_id' => $unit->id]) }}"
                           class="btn btn-sm btn-primary"><i class="bi bi-calendar-plus"></i><span class="d-none d-sm-inline ms-1">Booking</span></a>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-3">
                    <i class="bi bi-check-circle text-success me-1"></i> Semua unit AC terservis dengan baik. 🎉
                </div>
            @endforelse
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <strong>Jadwal Hari Ini</strong>
            <a href="{{ route('bookings.index') }}" class="small">Lihat semua</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 table-mobile-card">
                <thead class="table-light">
                    <tr>
                        <th>Jam</th><th>Customer</th><th>Layanan</th>
                        <th>Teknisi</th><th>Status</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($todayBookings as $booking)
                        <tr>
                            <td data-label="Jam">{{ $booking->scheduled_at->format('H:i') }}</td>
                            <td data-label="Customer">{{ $booking->customer->name }}</td>
                            <td data-label="Layanan">{{ $booking->service->name }}</td>
                            <td data-label="Teknisi">{{ $booking->technician?->user->name ?? '—' }}</td>
                            <td data-label="Status"><x-status-badge :status="$booking->status" /></td>
                            <td class="text-end cell-actions">
                                <a href="{{ route('bookings.show', $booking) }}"
                                   class="btn btn-sm btn-outline-primary">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">
                            Tidak ada jadwal untuk hari ini.
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
