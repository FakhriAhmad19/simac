@extends('layouts.app')
@section('title', 'Edit Layanan')

@section('content')
    <x-page-guide title="Panduan: Edit Layanan">
        <ul>
            <li>Perbarui nama, harga, atau durasi layanan lalu klik <strong>Simpan</strong>.</li>
            <li>Mengubah harga di sini <strong>tidak</strong> mengubah nominal pada booking yang sudah dibuat.</li>
        </ul>
    </x-page-guide>

    <div class="card"><div class="card-body">
        <div class="col-lg-7">
            <form method="POST" action="{{ route('services.update', $service) }}">
                @method('PUT')
                @include('services._form')
            </form>
        </div>
    </div></div>
@endsection
