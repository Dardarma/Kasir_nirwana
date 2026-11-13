<div class="form-group">
    <label>Nama Permission</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $permission?->name) }}" required>
</div>

<div class="form-group">
    <label>Kode Permission</label>
    @if ($permission)
        <input type="text" class="form-control" value="{{ $permission->slug }}" readonly>
        <small class="text-muted">Kode dikunci setelah dibuat agar aturan akses tidak terputus.</small>
    @else
        <input type="text" name="slug" class="form-control permission-slug" value="{{ old('slug') }}"
            placeholder="contoh: laporan.view" required>
        <small class="text-muted">Gunakan format modul.aksi, misalnya laporan.view.</small>
    @endif
</div>

<div class="form-group mb-0">
    <label>Penjelasan</label>
    <textarea name="description" class="form-control" rows="3" maxlength="500"
        placeholder="Jelaskan akses yang diberikan oleh permission ini">{{ old('description', $permission?->description) }}</textarea>
</div>
