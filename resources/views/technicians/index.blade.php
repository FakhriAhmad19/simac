@extends('layouts.app')
@section('title', 'Teknisi')

@section('content')
    <x-page-guide title="Panduan: Teknisi">
        <ul>
            <li>Halaman ini menampilkan daftar teknisi beserta <strong>status ketersediaannya</strong>.</li>
            <li>Hanya teknisi berstatus <strong>Available</strong> yang dapat ditugaskan ke sebuah booking.</li>
            @if (auth()->user()->isAdmin())
                <li>Ubah status pada dropdown lalu klik <strong>Simpan</strong> untuk memperbarui ketersediaan teknisi.</li>
            @endif
        </ul>
    </x-page-guide>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 table-mobile-card">
                <thead class="table-light">
                    <tr><th>Nama</th><th>Spesialisasi</th><th>Tugas Aktif</th><th>Status</th>
                        @if (auth()->user()->isAdmin())<th class="text-end">Ubah Status</th>@endif</tr>
                </thead>
                <tbody>
                    @forelse ($technicians as $technician)
                        <tr>
                            <td class="fw-semibold" data-label="Nama">{{ $technician->user->name }}</td>
                            <td class="text-muted" data-label="Spesialisasi">{{ $technician->specialization ?: '—' }}</td>
                            <td data-label="Tugas Aktif"><span class="badge text-bg-light">{{ $technician->active_count }}</span></td>
                            <td data-label="Status"><span class="badge text-bg-{{ $technician->status->color() }}">
                                {{ $technician->status->label() }}
                            </span></td>
                            @if (auth()->user()->isAdmin())
                                <td class="text-end cell-actions" data-label="Ubah Status">
                                    <form method="POST" action="{{ route('technicians.status', $technician) }}"
                                          class="d-inline-flex gap-1 justify-content-end">
                                        @csrf @method('PATCH')
                                        <select name="status" class="form-select form-select-sm" style="width:auto">
                                            @foreach (\App\Enums\TechnicianStatus::options() as $val => $label)
                                                <option value="{{ $val }}" @selected($technician->status->value === $val)>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn-sm btn-primary">Simpan</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Belum ada teknisi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
