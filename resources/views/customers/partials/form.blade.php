<div class="form-group">
    <label>Nama Customer</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $customer?->name) }}" required>
</div>
<div class="form-group">
    <label>Status</label>
    <select name="status" class="form-control" required>
        <option value="aktif" @selected(old('status', $customer?->status ?? 'aktif') === 'aktif')>Aktif</option>
        <option value="non_aktif" @selected(old('status', $customer?->status) === 'non_aktif')>Nonaktif</option>
    </select>
</div>
