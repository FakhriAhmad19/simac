@csrf
<div class="mb-3">
    <label class="form-label" for="name">Nama <span class="text-danger">*</span></label>
    <input id="name" name="name" value="{{ old('name', $customer->name ?? '') }}"
           class="form-control @error('name') is-invalid @enderror" required>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label class="form-label" for="phone">Nomor HP/WA <span class="text-danger">*</span></label>
    <input id="phone" name="phone" value="{{ old('phone', $customer->phone ?? '') }}"
           class="form-control @error('phone') is-invalid @enderror" required>
    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label class="form-label" for="address">Alamat</label>
    <textarea id="address" name="address" rows="2"
              class="form-control @error('address') is-invalid @enderror">{{ old('address', $customer->address ?? '') }}</textarea>
    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="d-flex gap-2">
    <button class="btn btn-primary">Simpan</button>
    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Batal</a>
</div>
