@extends('layouts.app')
@section('title', 'Customer Baru')

@section('content')
    <x-page-guide title="Panduan: Tambah Customer">
        <ul>
            <li><strong>Nama</strong> dan <strong>nomor HP/WA</strong> wajib diisi; alamat sangat disarankan untuk memudahkan kunjungan teknisi.</li>
            <li>Setelah tersimpan, Anda dapat menambahkan <strong>Unit AC</strong> dari halaman detail customer.</li>
        </ul>
    </x-page-guide>

    <div class="card"><div class="card-body">
        <div class="col-lg-7">
            @if (request('from') === 'booking')
                <div class="alert alert-info d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle"></i>
                    <span>Setelah disimpan, Anda akan diarahkan kembali ke halaman <strong>Buat Booking</strong> dengan customer ini terpilih.</span>
                </div>
            @endif
            <form method="POST" action="{{ route('customers.store') }}">
                @if (request('from') === 'booking')
                    <input type="hidden" name="from" value="booking">
                @endif
                @include('customers._form')
            </form>
        </div>
    </div></div>
@endsection
