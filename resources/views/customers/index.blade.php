@extends('layouts.app')
@section('title', 'Customer')

@section('actions')
    @if (auth()->user()->isAdmin())
        <a href="{{ route('customers.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Customer Baru
        </a>
    @endif
@endsection

@section('content')
    <x-page-guide title="Panduan: Data Customer">
        <ul>
            <li>Gunakan <strong>kolom pencarian</strong> untuk menemukan customer berdasarkan nama atau nomor HP.</li>
            <li>Kolom <strong>Unit AC</strong> dan <strong>Booking</strong> menampilkan jumlah unit dan riwayat servis tiap customer.</li>
            <li>Klik <strong>Detail</strong> untuk melihat profil, mengelola unit AC, dan riwayat servis customer.</li>
            <li>Klik <strong>Customer Baru</strong> untuk menambahkan data customer.</li>
        </ul>
    </x-page-guide>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="search" name="search" value="{{ $search }}" class="form-control"
                               placeholder="Cari nama atau nomor HP...">
                    </div>
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary"><i class="bi bi-search me-1"></i> Cari</button>
                </div>
                @if ($search)
                    <div class="col-auto"><a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">Reset</a></div>
                @endif
            </form>
        </div>
    </div>

    {{-- Desktop: data table --}}
    <div class="card d-none d-md-block">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Nama</th><th>HP/WA</th><th>Alamat</th>
                        <th class="text-center">Unit AC</th><th class="text-center">Booking</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr>
                            <td class="fw-semibold">{{ $customer->name }}</td>
                            <td>{{ $customer->phone }}</td>
                            <td class="text-muted">{{ Str::limit($customer->address, 28) ?: '—' }}</td>
                            <td class="text-center">{{ $customer->ac_units_count }}</td>
                            <td class="text-center">{{ $customer->bookings_count }}</td>
                            <td class="text-end">
                                <a href="{{ route('customers.show', $customer) }}"
                                   class="btn btn-sm btn-outline-primary">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data customer.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile: compact cards --}}
    <div class="d-md-none">
        @forelse ($customers as $customer)
            <div class="card mb-2">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div class="flex-grow-1" style="min-width:0">
                            <div class="fw-semibold text-truncate">{{ $customer->name }}</div>
                            <div class="text-muted small"><i class="bi bi-telephone me-1"></i>{{ $customer->phone }}</div>
                            @if ($customer->address)
                                <div class="text-muted small text-truncate"><i class="bi bi-geo-alt me-1"></i>{{ $customer->address }}</div>
                            @endif
                        </div>
                        <a href="{{ route('customers.show', $customer) }}"
                           class="btn btn-sm btn-outline-primary flex-shrink-0">Detail</a>
                    </div>
                    <div class="d-flex gap-2 mt-2 pt-2 border-top small text-muted">
                        <span><i class="bi bi-snow2 me-1"></i>{{ $customer->ac_units_count }} unit AC</span>
                        <span><i class="bi bi-calendar-check me-1"></i>{{ $customer->bookings_count }} booking</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center text-muted py-4">Belum ada data customer.</div>
            </div>
        @endforelse
    </div>

    @if ($customers->hasPages())
        <div class="mt-3 d-flex justify-content-center justify-content-md-start">
            {{ $customers->links() }}
        </div>
    @endif
@endsection
