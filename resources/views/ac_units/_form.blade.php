@csrf
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="brand">Merek</label>
        <input id="brand" name="brand" value="{{ old('brand', $acUnit->brand ?? '') }}" class="form-control">
    </div>
    <div class="col-md-6">
        <label class="form-label" for="type">Tipe</label>
        <input id="type" name="type" value="{{ old('type', $acUnit->type ?? '') }}"
               class="form-control" placeholder="Split / Cassette / Standing">
    </div>
    <div class="col-md-6">
        <label class="form-label" for="capacity_pk">Kapasitas (PK)</label>
        <input id="capacity_pk" name="capacity_pk" value="{{ old('capacity_pk', $acUnit->capacity_pk ?? '') }}"
               class="form-control" placeholder="0.5 / 1 / 2">
    </div>
    <div class="col-md-6">
        <label class="form-label" for="location_note">Catatan Lokasi</label>
        <input id="location_note" name="location_note"
               value="{{ old('location_note', $acUnit->location_note ?? '') }}" class="form-control">
    </div>
</div>
<div class="d-flex gap-2 mt-3">
    <button class="btn btn-primary">Simpan</button>
    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Batal</a>
</div>
