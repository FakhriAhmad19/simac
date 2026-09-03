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

    {{-- Desktop: data table --}}
    <div class="card d-none d-md-block">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Nama</th><th>Spesialisasi</th><th>Tugas Aktif</th><th>Status</th>
                        @if (auth()->user()->isAdmin())<th class="text-end">Ubah Status</th>@endif</tr>
                </thead>
                <tbody>
                    @forelse ($technicians as $technician)
                        <tr>
                            <td class="fw-semibold">{{ $technician->user->name }}</td>
                            <td class="text-muted">{{ $technician->specialization ?: '—' }}</td>
                            <td><span class="badge text-bg-light">{{ $technician->active_count }}</span></td>
                            <td><span class="badge text-bg-{{ $technician->status->color() }}">
                                {{ $technician->status->label() }}
                            </span></td>
                            @if (auth()->user()->isAdmin())
                                <td class="text-end">
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

    {{-- Mobile: compact cards --}}
    <div class="d-md-none">
        @forelse ($technicians as $technician)
            <div class="card mb-2">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div class="flex-grow-1" style="min-width:0">
                            <div class="fw-semibold text-truncate">{{ $technician->user->name }}</div>
                            <div class="text-muted small text-truncate">{{ $technician->specialization ?: 'Tanpa spesialisasi' }}</div>
                            <div class="small text-muted mt-1">
                                <i class="bi bi-list-task me-1"></i>{{ $technician->active_count }} tugas aktif
                            </div>
                        </div>
                        <span class="badge text-bg-{{ $technician->status->color() }} flex-shrink-0">
                            {{ $technician->status->label() }}
                        </span>
                    </div>
                    @if (auth()->user()->isAdmin())
                        <form method="POST" action="{{ route('technicians.status', $technician) }}"
                              class="d-flex gap-1 mt-2 pt-2 border-top">
                            @csrf @method('PATCH')
                            <select name="status" class="form-select form-select-sm">
                                @foreach (\App\Enums\TechnicianStatus::options() as $val => $label)
                                    <option value="{{ $val }}" @selected($technician->status->value === $val)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <button class="btn btn-sm btn-primary flex-shrink-0">Simpan</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center text-muted py-4">Belum ada teknisi.</div>
            </div>
        @endforelse
    </div>
@endsection
