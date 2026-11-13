@php
    $selectedPermissions = collect(old('permissions', $role?->permissions->pluck('id')->all() ?? []))
        ->map(fn ($id) => (int) $id);
    $isSuperAdmin = $role?->slug === 'super-admin';
@endphp

<div class="form-row">
    <div class="form-group col-md-6">
        <label>Nama Role</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $role?->name) }}" required>
    </div>
    <div class="form-group col-md-6">
        <label>Kode Role</label>
        <input type="text" name="slug" class="form-control role-slug"
            value="{{ old('slug', $role?->slug) }}" placeholder="contoh: kasir" {{ $isSuperAdmin ? 'readonly' : '' }} required>
        <small class="text-muted">Huruf kecil, angka, titik, atau tanda hubung.</small>
    </div>
</div>

<div class="form-group">
    <label>Deskripsi</label>
    <textarea name="description" class="form-control" rows="2" maxlength="500">{{ old('description', $role?->description) }}</textarea>
</div>

<div class="form-group">
    <label>Status</label>
    @if ($isSuperAdmin)
        <input type="hidden" name="is_active" value="1">
        <input type="text" class="form-control" value="Aktif" readonly>
    @else
        <select name="is_active" class="form-control" required>
            <option value="1" @selected((string) old('is_active', (int) ($role?->is_active ?? true)) === '1')>Aktif</option>
            <option value="0" @selected((string) old('is_active', (int) ($role?->is_active ?? true)) === '0')>Nonaktif</option>
        </select>
    @endif
</div>

<fieldset class="form-group mb-0">
    <legend class="h6 font-weight-bold">Permission</legend>
    <div class="permission-grid border rounded p-2">
        @forelse ($permissions as $permission)
            <div class="custom-control custom-checkbox permission-item">
                <input type="checkbox" class="custom-control-input"
                    id="permission-{{ $formId }}-{{ $permission->id }}" name="permissions[]"
                    value="{{ $permission->id }}" @checked($selectedPermissions->contains($permission->id))>
                <label class="custom-control-label" for="permission-{{ $formId }}-{{ $permission->id }}">
                    <span class="d-block">{{ $permission->name }}</span>
                    <small class="text-muted">{{ $permission->slug }}</small>
                </label>
            </div>
        @empty
            <p class="text-muted mb-0">Belum ada permission. Jalankan seeder akses terlebih dahulu.</p>
        @endforelse
    </div>
</fieldset>
