@extends('layouts.app')
@section('title', 'Booking')

@section('actions')
    @if (auth()->user()->isAdmin())
        <a href="{{ route('bookings.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Booking Baru
        </a>
    @endif
@endsection

@section('content')
    <x-page-guide title="Panduan: Daftar Booking">
        <ul>
            <li>Gunakan <strong>kolom pencarian</strong> untuk mencari booking berdasarkan ID, nama customer, no. HP, layanan, atau nama teknisi.</li>
            <li>Gunakan <strong>filter status</strong> untuk menyaring booking (Pending, Assigned, Completed, dll).</li>
            <li>Klik <strong>Detail</strong> untuk melihat riwayat, menugaskan teknisi, mencatat pembayaran, atau membatalkan booking.</li>
            <li>Klik <strong>Booking Baru</strong> untuk membuat pesanan servis baru.</li>
        </ul>
    </x-page-guide>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" value="{{ $search }}" class="form-control"
                               placeholder="Cari ID, customer, no. HP, layanan, atau teknisi...">
                    </div>
                </div>
                <div class="col-8 col-md-3">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua status</option>
                        @foreach ($statuses as $val => $label)
                            <option value="{{ $val }}" @selected($status === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-4 col-md-auto">
                    <button class="btn btn-primary w-100"><i class="bi bi-search me-1"></i> Cari</button>
                </div>
                @if ($search !== '' || $status)
                    <div class="col-auto"><a href="{{ route('bookings.index') }}" class="btn btn-outline-secondary">Reset</a></div>
                @endif
            </form>
        </div>
    </div>

    {{-- Desktop: data table --}}
    <div class="card d-none d-md-block">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>#</th><th>Jadwal</th><th>Customer</th><th>Layanan</th>
                        <th>Teknisi</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $booking)
                        <tr>
                            <td class="text-muted">#{{ $booking->id }}</td>
                            <td>{{ $booking->scheduled_at->format('d M Y H:i') }}</td>
                            <td>{{ $booking->customer->name }}</td>
                            <td>{{ $booking->service->name }}</td>
                            <td>{{ $booking->technician?->user->name ?? '—' }}</td>
                            <td><x-status-badge :status="$booking->status" /></td>
                            <td class="text-end">
                                <a href="{{ route('bookings.show', $booking) }}"
                                   class="btn btn-sm btn-outline-primary">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Belum ada booking.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile: compact cards --}}
    <div class="d-md-none">
        @forelse ($bookings as $booking)
            <div class="card mb-2">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div class="flex-grow-1" style="min-width:0">
                            <div class="fw-semibold text-truncate">{{ $booking->customer->name }}</div>
                            <div class="text-muted small text-truncate">#{{ $booking->id }} · {{ $booking->service->name }}</div>
                        </div>
                        <x-status-badge :status="$booking->status" />
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top gap-2">
                        <div class="small text-muted" style="min-width:0">
                            <div class="text-truncate"><i class="bi bi-calendar-event me-1"></i>{{ $booking->scheduled_at->format('d M Y H:i') }}</div>
                            <div class="text-truncate"><i class="bi bi-person me-1"></i>{{ $booking->technician?->user->name ?? 'Belum ditugaskan' }}</div>
                        </div>
                        <a href="{{ route('bookings.show', $booking) }}"
                           class="btn btn-sm btn-outline-primary flex-shrink-0">Detail</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center text-muted py-4">Belum ada booking.</div>
            </div>
        @endforelse
    </div>

    @if ($bookings->hasPages())
        <div class="mt-3 d-flex justify-content-center justify-content-md-start">
            {{ $bookings->links() }}
        </div>
    @endif
@endsection
