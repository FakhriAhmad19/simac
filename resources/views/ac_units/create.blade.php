@extends('layouts.app')
@section('title', 'Tambah Unit AC')

@section('content')
    <x-page-guide title="Panduan: Tambah Unit AC">
        <ul>
            <li>Catat <strong>merek, tipe, kapasitas (PK)</strong>, dan lokasi pemasangan unit untuk memudahkan teknisi.</li>
            <li>Unit yang tersimpan akan bisa dipilih saat membuat booking servis untuk customer ini.</li>
        </ul>
    </x-page-guide>

    <div class="card"><div class="card-body">
        <p class="text-muted">Customer: <strong>{{ $customer->name }}</strong></p>
        @if (request('from') === 'booking')
            <div class="alert alert-info d-flex align-items-center gap-2">
                <i class="bi bi-info-circle"></i>
                <span>Setelah disimpan, Anda akan diarahkan kembali ke halaman <strong>Buat Booking</strong> dengan unit ini terpilih.</span>
            </div>
        @endif
        <form method="POST" action="{{ route('ac-units.store', $customer) }}">
            @if (request('from') === 'booking')
                <input type="hidden" name="from" value="booking">
            @endif
            @include('ac_units._form')
        </form>
    </div></div>
@endsection
