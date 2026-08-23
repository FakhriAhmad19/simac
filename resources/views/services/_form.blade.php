@csrf
<div class="mb-3">
    <label class="form-label" for="name">Nama Layanan <span class="text-danger">*</span></label>
    <input id="name" name="name" value="{{ old('name', $service->name ?? '') }}"
           class="form-control @error('name') is-invalid @enderror" required>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label class="form-label" for="description">Deskripsi</label>
    <textarea id="description" name="description" rows="3"
              class="form-control">{{ old('description', $service->description ?? '') }}</textarea>
</div>
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="price">Harga (Rp) <span class="text-danger">*</span></label>
        <input id="price" name="price" type="number" step="0.01" min="0"
               value="{{ old('price', $service->price ?? '') }}"
               class="form-control @error('price') is-invalid @enderror" required>
        @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="estimated_duration">Estimasi Durasi (menit)</label>
        <input id="estimated_duration" name="estimated_duration" type="number" min="0"
               value="{{ old('estimated_duration', $service->estimated_duration ?? '') }}" class="form-control">
    </div>
</div>
<div class="d-flex gap-2 mt-3">
    <button class="btn btn-primary">Simpan</button>
    <a href="{{ route('services.index') }}" class="btn btn-outline-secondary">Batal</a>
</div>
