@extends('layouts.app')
@section('title', 'Laporan')

@section('content')
    <x-page-guide title="Panduan: Laporan">
        <ul>
            <li>Atur <strong>rentang tanggal</strong> lalu klik <strong>Terapkan</strong> untuk menyaring data laporan.</li>
            <li>Kartu ringkasan menampilkan <strong>total pendapatan</strong> dan jumlah booking pada periode tersebut.</li>
            <li>Gunakan laporan ini untuk memantau <strong>performa layanan</strong> dari waktu ke waktu.</li>
        </ul>
    </x-page-guide>

    <div class="card mb-3"><div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-4 col-md-3">
                <label class="form-label small">Dari tanggal</label>
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="form-control">
            </div>
            <div class="col-sm-4 col-md-3">
                <label class="form-label small">Sampai tanggal</label>
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="form-control">
            </div>
            <div class="col-auto">
                <button class="btn btn-primary">Terapkan</button>
            </div>
        </form>
    </div></div>

    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100"><div class="card-body">
                <div class="text-muted small">Total Booking</div>
                <div class="value">{{ $totalBookings }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100"><div class="card-body">
                <div class="text-muted small">Pendapatan (Lunas)</div>
                <div class="value">Rp {{ number_format($revenue, 0, ',', '.') }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100"><div class="card-body">
                <div class="text-muted small">Belum Lunas</div>
                <div class="value text-danger">Rp {{ number_format($unpaidTotal, 0, ',', '.') }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100"><div class="card-body">
                <div class="text-muted small">Periode</div>
                <div class="fw-semibold mt-2">{{ $from->format('d M') }} – {{ $to->format('d M Y') }}</div>
            </div></div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header bg-white"><strong>Booking per Status</strong></div>
                <ul class="list-group list-group-flush">
                    @foreach ($statuses as $val => $label)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{{ $label }}</span>
                            <span class="badge text-bg-secondary">{{ $statusCounts[$val] ?? 0 }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header bg-white"><strong>Performa Teknisi</strong></div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-mobile-card">
                        <thead class="table-light">
                            <tr><th>Teknisi</th><th class="text-center">Total Tugas</th><th class="text-center">Selesai</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($technicianPerformance as $t)
                                <tr>
                                    <td data-label="Teknisi">{{ $t->user->name }}</td>
                                    <td class="text-center" data-label="Total Tugas">{{ $t->total_count }}</td>
                                    <td class="text-center" data-label="Selesai"><span class="badge text-bg-success">{{ $t->completed_count }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-4">Belum ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
