@extends('layouts.app')
@section('title', 'Edit Unit AC')

@section('content')
    <x-page-guide title="Panduan: Edit Unit AC">
        <ul>
            <li>Perbarui detail unit AC lalu klik <strong>Simpan</strong>.</li>
            <li>Klik <strong>Batal</strong> untuk kembali ke halaman customer tanpa menyimpan.</li>
        </ul>
    </x-page-guide>

    <div class="card"><div class="card-body">
        <p class="text-muted">Customer: <strong>{{ $acUnit->customer->name }}</strong></p>
        <form method="POST" action="{{ route('ac-units.update', $acUnit) }}">
            @method('PUT')
            @include('ac_units._form')
        </form>
    </div></div>
@endsection
