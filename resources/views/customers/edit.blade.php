@extends('layouts.app')
@section('title', 'Edit Customer')

@section('content')
    <x-page-guide title="Panduan: Edit Customer">
        <ul>
            <li>Perbarui data customer lalu klik <strong>Simpan</strong>. Perubahan tidak memengaruhi riwayat booking yang sudah ada.</li>
            <li>Klik <strong>Batal</strong> untuk kembali tanpa menyimpan perubahan.</li>
        </ul>
    </x-page-guide>

    <div class="card"><div class="card-body">
        <div class="col-lg-7">
            <form method="POST" action="{{ route('customers.update', $customer) }}">
                @method('PUT')
                @include('customers._form')
            </form>
        </div>
    </div></div>
@endsection
