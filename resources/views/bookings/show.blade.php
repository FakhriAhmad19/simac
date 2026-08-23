@extends('layouts.app')
@section('title', 'Booking #'.$booking->id)

@php
    use App\Enums\BookingStatus;
    $user = auth()->user();
    $isAdmin = $user->isAdmin();
    $isOwnTechnician = $user->isTechnician() && $booking->technician?->user_id === $user->id;
    $terminal = in_array($booking->status, [BookingStatus::Completed, BookingStatus::Cancelled], true);
    $nextOptions = $booking->status->technicianNextOptions();
    $canUpdateStatus = ($isAdmin || $isOwnTechnician) && ! empty($nextOptions);

    // Status-aware WhatsApp message to the customer.
    $waName = $booking->customer->name;
    $waService = $booking->service->name;
    $waDate = $booking->scheduled_at->translatedFormat('d M Y, H:i');
    $waTech = $booking->technician?->user->name;
    $waMessage = match ($booking->status) {
        BookingStatus::Completed => "Halo {$waName}, terima kasih telah menggunakan layanan SIMAC. Servis \"{$waService}\" pada {$waDate} telah selesai. Semoga puas dengan layanan kami! 🙏",
        BookingStatus::Assigned, BookingStatus::OnTheWay, BookingStatus::InProgress =>
            "Halo {$waName}, booking servis \"{$waService}\" Anda dijadwalkan pada {$waDate}"
            .($waTech ? ". Teknisi kami: {$waTech}" : '').". Terima kasih. — SIMAC",
        BookingStatus::Cancelled => "Halo {$waName}, mohon maaf booking servis \"{$waService}\" pada {$waDate} dibatalkan. Silakan hubungi kami untuk penjadwalan ulang. — SIMAC",
        default => "Halo {$waName}, booking servis \"{$waService}\" Anda telah kami terima untuk {$waDate}. Kami akan segera memprosesnya. Terima kasih. — SIMAC",
    };
@endphp

@section('actions')
    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
@endsection

@section('content')
    <x-page-guide title="Panduan: Detail Booking">
        <ul>
            <li>Panel kiri berisi <strong>informasi booking</strong> dan <strong>riwayat perubahan status</strong>.</li>
            <li>Panel kanan berisi aksi: <strong>ubah status</strong>, <strong>tugaskan teknisi</strong>, dan <strong>batalkan booking</strong> (tombol merah).</li>
            <li>Setelah status <strong>Completed</strong>, akan muncul form <strong>Pembayaran</strong> dan <strong>Ulasan</strong>.</li>
            <li>Aksi yang tampil menyesuaikan dengan <strong>peran</strong> Anda (Admin, Teknisi, atau Owner).</li>
        </ul>
    </x-page-guide>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card mb-3">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <strong>Detail Booking</strong>
                    <x-status-badge :status="$booking->status" />
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-muted">Customer</dt>
                        <dd class="col-sm-8">
                            <a href="{{ route('customers.show', $booking->customer) }}">{{ $booking->customer->name }}</a>
                            <div class="small text-muted d-flex align-items-center gap-2 flex-wrap">
                                <span>{{ $booking->customer->phone }}</span>
                                @if ($isAdmin && $booking->customer->phone)
                                    <a href="{{ $booking->customer->waUrl($waMessage) }}" target="_blank" rel="noopener"
                                       class="btn btn-sm btn-success py-0 px-2">
                                        <i class="bi bi-whatsapp me-1"></i> Kirim WA
                                    </a>
                                @endif
                            </div>
                        </dd>
                        <dt class="col-sm-4 text-muted">Layanan</dt>
                        <dd class="col-sm-8">{{ $booking->service->name }}
                            <span class="text-muted">(Rp {{ number_format($booking->service->price, 0, ',', '.') }})</span>
                        </dd>
                        <dt class="col-sm-4 text-muted">Unit AC</dt>
                        <dd class="col-sm-8">{{ $booking->acUnit?->label() ?? '—' }}</dd>
                        <dt class="col-sm-4 text-muted">Jadwal</dt>
                        <dd class="col-sm-8">{{ $booking->scheduled_at->format('d M Y, H:i') }}</dd>
                        <dt class="col-sm-4 text-muted">Teknisi</dt>
                        <dd class="col-sm-8">{{ $booking->technician?->user->name ?? 'Belum ditugaskan' }}</dd>
                        <dt class="col-sm-4 text-muted">Catatan</dt>
                        <dd class="col-sm-8">{{ $booking->notes ?: '—' }}</dd>
                        <dt class="col-sm-4 text-muted">Dibuat oleh</dt>
                        <dd class="col-sm-8">{{ $booking->creator->name ?? '—' }} · {{ $booking->created_at->format('d M Y H:i') }}</dd>
                    </dl>
                </div>
            </div>

            {{-- Status timeline --}}
            <div class="card">
                <div class="card-header bg-white"><strong>Riwayat Status</strong></div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        @forelse ($booking->histories as $history)
                            <li class="d-flex mb-3">
                                <div class="me-3 text-center" style="width:1.5rem">
                                    <i class="bi bi-circle-fill text-{{ $history->status->color() }}"></i>
                                </div>
                                <div>
                                    <div><x-status-badge :status="$history->status" /></div>
                                    <div class="small text-muted">
                                        {{ $history->created_at?->format('d M Y H:i') }}
                                        · oleh {{ $history->changedBy->name ?? '—' }}
                                    </div>
                                    @if ($history->notes)
                                        <div class="small">{{ $history->notes }}</div>
                                    @endif
                                </div>
                            </li>
                        @empty
                            <li class="text-muted">Belum ada riwayat.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            {{-- Update status (technician / admin) --}}
            @if ($canUpdateStatus)
                <div class="card mb-3">
                    <div class="card-header bg-white"><strong>Perbarui Status</strong></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('bookings.status', $booking) }}">
                            @csrf @method('PATCH')
                            <div class="mb-2">
                                <select name="status" class="form-select" required>
                                    @foreach ($nextOptions as $opt)
                                        <option value="{{ $opt->value }}">{{ $opt->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <textarea name="notes" rows="2" class="form-control mb-2" placeholder="Catatan (opsional)"></textarea>
                            <button class="btn btn-primary w-100">Simpan Status</button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Assign technician (admin only, non-terminal) --}}
            @if ($isAdmin && ! $terminal)
                <div class="card mb-3">
                    <div class="card-header bg-white">
                        <strong>{{ $booking->technician ? 'Ganti Teknisi' : 'Tugaskan Teknisi' }}</strong>
                    </div>
                    <div class="card-body">
                        @if ($availableTechnicians->isEmpty() && ! $booking->technician)
                            <p class="text-muted small mb-0">Tidak ada teknisi yang available saat ini.</p>
                        @else
                            <form method="POST" action="{{ route('bookings.assign', $booking) }}">
                                @csrf
                                <div class="mb-2">
                                    <select name="technician_id" class="form-select" required>
                                        <option value="">— Pilih teknisi —</option>
                                        @foreach ($availableTechnicians as $tech)
                                            <option value="{{ $tech->id }}">{{ $tech->user->name }}
                                                @if ($tech->specialization) — {{ $tech->specialization }} @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button class="btn btn-primary w-100">Tugaskan</button>
                            </form>
                        @endif
                    </div>
                </div>

                {{-- Cancel --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <form method="POST" action="{{ route('bookings.cancel', $booking) }}"
                              onsubmit="return confirm('Batalkan booking ini?')">
                            @csrf
                            <button class="btn btn-danger w-100">
                                <i class="bi bi-x-circle me-1"></i> Batalkan Booking
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Payment (admin, when completed) --}}
            @if ($isAdmin && $booking->status === BookingStatus::Completed)
                <div class="card mb-3">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <strong>Pembayaran</strong>
                        @if ($booking->payment)
                            <span class="badge text-bg-{{ $booking->payment->status->color() }}">
                                {{ $booking->payment->status->label() }}
                            </span>
                        @endif
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('payments.store', $booking) }}">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label small">Jumlah (Rp)</label>
                                <input name="amount" type="number" step="0.01" min="0" class="form-control"
                                       value="{{ old('amount', $booking->payment->amount ?? $booking->service->price) }}" required>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label small">Metode</label>
                                    <select name="payment_method" class="form-select">
                                        @foreach (\App\Enums\PaymentMethod::options() as $val => $label)
                                            <option value="{{ $val }}"
                                                @selected(($booking->payment->payment_method->value ?? '') === $val)>
                                                {{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="unpaid" @selected(($booking->payment->status->value ?? 'unpaid') === 'unpaid')>Belum Lunas</option>
                                        <option value="paid" @selected(($booking->payment->status->value ?? '') === 'paid')>Lunas</option>
                                    </select>
                                </div>
                            </div>
                            <button class="btn btn-primary w-100">Simpan Pembayaran</button>
                        </form>
                    </div>
                </div>

                {{-- Review (admin, when completed) --}}
                <div class="card mb-3">
                    <div class="card-header bg-white"><strong>Ulasan Customer</strong></div>
                    <div class="card-body">
                        @if ($booking->review)
                            <div class="mb-2">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i class="bi {{ $i <= $booking->review->rating ? 'bi-star-fill text-warning' : 'bi-star text-muted' }}"></i>
                                @endfor
                            </div>
                            <p class="mb-2">{{ $booking->review->comment ?: '—' }}</p>
                            <hr>
                            <p class="small text-muted mb-2">Perbarui ulasan:</p>
                        @endif
                        <form method="POST" action="{{ route('reviews.store', $booking) }}">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label small">Rating (1-5)</label>
                                <select name="rating" class="form-select" required>
                                    @for ($i = 5; $i >= 1; $i--)
                                        <option value="{{ $i }}" @selected(($booking->review->rating ?? 0) === $i)>
                                            {{ $i }} bintang</option>
                                    @endfor
                                </select>
                            </div>
                            <textarea name="comment" rows="2" class="form-control mb-2"
                                      placeholder="Komentar customer">{{ $booking->review->comment ?? '' }}</textarea>
                            <button class="btn btn-primary w-100">Simpan Ulasan</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
