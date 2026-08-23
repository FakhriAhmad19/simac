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

    <div class="card">
        <div class="card-header bg-white">
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
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 table-mobile-card">
                <thead class="table-light">
                    <tr><th>Nama</th><th>HP/WA</th><th class="d-none d-md-table-cell">Alamat</th>
                        <th class="text-center">Unit AC</th><th class="text-center">Booking</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr>
                            <td class="fw-semibold" data-label="Nama">{{ $customer->name }}</td>
                            <td data-label="HP/WA">{{ $customer->phone }}</td>
                            <td class="text-muted d-none d-md-table-cell" data-label="Alamat">{{ Str::limit($customer->address, 28) ?: '—' }}</td>
                            <td class="text-center" data-label="Unit AC">{{ $customer->ac_units_count }}</td>
                            <td class="text-center" data-label="Booking">{{ $customer->bookings_count }}</td>
                            <td class="text-end cell-actions">
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
        <div class="card-footer bg-white">{{ $customers->links() }}</div>
    </div>
@endsection
