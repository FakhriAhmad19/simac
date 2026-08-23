@extends('layouts.app')
@section('title', 'Booking Baru')

@section('content')
    <x-page-guide title="Panduan: Buat Booking">
        <ul>
            <li>Pilih <strong>Customer</strong> terlebih dahulu — daftar <strong>Unit AC</strong> akan menyesuaikan dengan customer tersebut.</li>
            <li>Pilih <strong>Layanan</strong> dan tentukan <strong>jadwal</strong> (tanggal &amp; jam) pengerjaan.</li>
            <li>Isi <strong>catatan</strong> bila ada permintaan khusus dari customer (opsional).</li>
            <li>Booking baru otomatis berstatus <strong>Pending</strong>; teknisi ditugaskan dari halaman Detail booking.</li>
        </ul>
    </x-page-guide>

    <div class="card"><div class="card-body">
        <div class="col-lg-8">
            @if ($customers->isEmpty() || $services->isEmpty())
                <div class="alert alert-warning">
                    Pastikan minimal ada 1 customer dan 1 layanan sebelum membuat booking.
                    <a href="{{ route('customers.create') }}">Tambah customer</a> ·
                    <a href="{{ route('services.create') }}">Tambah layanan</a>
                </div>
            @endif
            <form method="POST" action="{{ route('bookings.store') }}">
                @csrf
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="form-label mb-0" for="customer_id">Customer <span class="text-danger">*</span></label>
                        <a href="{{ route('customers.create', ['from' => 'booking']) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-person-plus me-1"></i> Daftar customer baru
                        </a>
                    </div>
                    <select id="customer_id" name="customer_id"
                            class="form-select mt-1 @error('customer_id') is-invalid @enderror"
                            data-units-url="{{ url('customers') }}" required>
                        <option value="">— Pilih customer —</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}"
                                @selected((int) old('customer_id', $selectedCustomer) === $customer->id)>
                                {{ $customer->name }} — {{ $customer->phone }}
                            </option>
                        @endforeach
                    </select>
                    @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Customer belum ada di daftar? Klik <strong>Daftar customer baru</strong> — setelah tersimpan Anda akan kembali ke sini dengan customer tersebut otomatis terpilih.</div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="form-label mb-0" for="ac_unit_id">Unit AC</label>
                        <a id="add_unit_link" href="#" class="btn btn-sm btn-outline-primary d-none">
                            <i class="bi bi-plus-lg me-1"></i> Tambah unit AC
                        </a>
                    </div>
                    <select id="ac_unit_id" name="ac_unit_id"
                            class="form-select mt-1 @error('ac_unit_id') is-invalid @enderror">
                        <option value="">— Tidak spesifik —</option>
                    </select>
                    @error('ac_unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Pilih customer dulu, lalu daftar unitnya muncul. Unit belum terdaftar? Klik <strong>Tambah unit AC</strong> — setelah tersimpan Anda kembali ke sini dengan unit terpilih.</div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="service_id">Layanan <span class="text-danger">*</span></label>
                        <select id="service_id" name="service_id"
                                class="form-select @error('service_id') is-invalid @enderror" required>
                            <option value="">— Pilih layanan —</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}" @selected((int) old('service_id') === $service->id)>
                                    {{ $service->name }} (Rp {{ number_format($service->price, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                        @error('service_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="scheduled_at">Jadwal <span class="text-danger">*</span></label>
                        <input id="scheduled_at" name="scheduled_at" type="datetime-local"
                               value="{{ old('scheduled_at') }}"
                               class="form-control @error('scheduled_at') is-invalid @enderror" required>
                        @error('scheduled_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="notes">Catatan (lokasi / keluhan)</label>
                    <textarea id="notes" name="notes" rows="2" class="form-control">{{ old('notes') }}</textarea>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-primary">Buat Booking</button>
                    <a href="{{ route('bookings.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div></div>
@endsection

@push('scripts')
<script>
    const customerSelect = document.getElementById('customer_id');
    const unitSelect = document.getElementById('ac_unit_id');
    const addUnitLink = document.getElementById('add_unit_link');
    const base = customerSelect.dataset.unitsUrl;
    const preselectedUnit = @json(old('ac_unit_id', request('ac_unit_id')));

    function updateAddUnitLink(customerId) {
        if (customerId) {
            addUnitLink.href = `${base}/${customerId}/units/create?from=booking`;
            addUnitLink.classList.remove('d-none');
        } else {
            addUnitLink.classList.add('d-none');
        }
    }

    async function loadUnits(customerId, selected) {
        unitSelect.innerHTML = '<option value="">— Tidak spesifik —</option>';
        if (!customerId) return;
        try {
            const res = await fetch(`${base}/${customerId}/units-json`, {
                headers: { 'Accept': 'application/json' }
            });
            const units = await res.json();
            for (const u of units) {
                const opt = document.createElement('option');
                opt.value = u.id;
                opt.textContent = u.label;
                if (String(selected) === String(u.id)) opt.selected = true;
                unitSelect.appendChild(opt);
            }
        } catch (e) { /* ignore */ }
    }

    customerSelect.addEventListener('change', () => {
        updateAddUnitLink(customerSelect.value);
        loadUnits(customerSelect.value, null);
    });

    updateAddUnitLink(customerSelect.value);
    if (customerSelect.value) loadUnits(customerSelect.value, preselectedUnit);
</script>
@endpush
