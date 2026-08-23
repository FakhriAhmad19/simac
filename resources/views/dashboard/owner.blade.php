@extends('layouts.app')
@section('title', 'Dashboard Owner')

@section('actions')
    <a href="{{ route('reports.index') }}" class="btn btn-outline-primary">
        <i class="bi bi-bar-chart-line me-1"></i> Laporan Detail
    </a>
@endsection

@section('content')
    @php use App\Enums\BookingStatus; @endphp

    <x-page-guide title="Panduan: Dashboard">
        <ul>
            <li>Kartu ringkasan menampilkan <strong>pendapatan</strong> dan performa bisnis secara sekilas.</li>
            <li>Buka menu <strong>Laporan</strong> di sidebar untuk analisis per rentang tanggal.</li>
            <li>Anda juga dapat memantau data <strong>Booking, Customer, Layanan,</strong> dan <strong>Teknisi</strong> dari sidebar.</li>
        </ul>
    </x-page-guide>
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100"><div class="card-body">
                <div class="text-muted small">Total Pendapatan (Lunas)</div>
                <div class="value">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
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
                <div class="text-muted small">Selesai</div>
                <div class="value">{{ $statusCounts[BookingStatus::Completed->value] ?? 0 }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100"><div class="card-body">
                <div class="text-muted small">Dibatalkan</div>
                <div class="value">{{ $statusCounts[BookingStatus::Cancelled->value] ?? 0 }}</div>
            </div></div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header bg-white"><strong>Pendapatan 6 Bulan Terakhir</strong></div>
                <div class="card-body">
                    @php $max = max($monthlyRevenue->max('revenue'), 1); @endphp
                    @foreach ($monthlyRevenue as $row)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span>{{ $row['label'] }}</span>
                                <span class="fw-semibold">Rp {{ number_format($row['revenue'], 0, ',', '.') }}</span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-primary" role="progressbar"
                                     style="width: {{ round($row['revenue'] / $max * 100) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header bg-white"><strong>Performa Teknisi</strong></div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-mobile-card">
                        <thead class="table-light"><tr><th>Teknisi</th><th class="text-end">Selesai</th></tr></thead>
                        <tbody>
                            @forelse ($technicianPerformance as $t)
                                <tr>
                                    <td data-label="Teknisi">{{ $t->user->name }}</td>
                                    <td class="text-end" data-label="Selesai"><span class="badge text-bg-success">{{ $t->completed_count }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted py-4">Belum ada data teknisi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
