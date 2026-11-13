<div class="form-group">
    <label>Nama</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $user?->name) }}" required>
</div>
<div class="form-group">
    <label>Username</label>
    <input type="text" name="username" class="form-control" value="{{ old('username', $user?->username) }}"
        autocomplete="off" required>
</div>
<div class="form-row">
    <div class="form-group col-md-6">
        <label>Password {{ $user ? '(opsional)' : '' }}</label>
        <input type="password" name="password" class="form-control" autocomplete="new-password"
            {{ $user ? '' : 'required' }}>
    </div>
    <div class="form-group col-md-6">
        <label>Konfirmasi Password</label>
        <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password"
            {{ $user ? '' : 'required' }}>
    </div>
</div>
<div class="form-group">
    <label>Status</label>
    <select name="status" class="form-control" required>
        <option value="aktif" @selected(old('status', $user?->status ?? 'aktif') === 'aktif')>Aktif</option>
        <option value="non_aktif" @selected(old('status', $user?->status) === 'non_aktif')>Nonaktif</option>
    </select>
</div>
@if ($canManageRoles)
    <div class="form-group">
        <label>Role</label>
        <select name="roles[]" class="form-control" multiple size="{{ max(2, min(5, $roles->count())) }}">
            @foreach ($roles as $role)
                <option value="{{ $role->id }}" @selected($user?->roles->contains($role->id))>
                    {{ $role->name }}
                </option>
            @endforeach
        </select>
        @if ($roles->isEmpty())
            <small class="text-muted">Belum ada role aktif. Jalankan seeder terlebih dahulu.</small>
        @else
            <small class="text-muted">Gunakan Ctrl atau Command untuk memilih lebih dari satu role.</small>
        @endif
    </div>
@endif
