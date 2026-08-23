@extends('layouts.app')
@section('title', 'Layanan Baru')

@section('content')
    <x-page-guide title="Panduan: Tambah Layanan">
        <ul>
            <li>Tentukan <strong>nama layanan</strong>, <strong>harga</strong>, dan <strong>estimasi durasi</strong> pengerjaan.</li>
            <li>Layanan yang disimpan akan tersedia sebagai pilihan saat membuat booking.</li>
        </ul>
    </x-page-guide>

    <div class="card"><div class="card-body">
        <div class="col-lg-7">
            <form method="POST" action="{{ route('services.store') }}">
                @include('services._form')
            </form>
        </div>
    </div></div>
@endsection
