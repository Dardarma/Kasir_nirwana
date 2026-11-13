@extends('layout')

@section('content')
    <div class="alert alert-info">
        <h5><i class="fas fa-info-circle mr-1"></i> Tentang Permission</h5>
        <p class="mb-1">Permission adalah daftar izin yang nantinya dipilih pada sebuah role.</p>
        <p class="mb-0"><strong>Catatan:</strong> permission baru harus dihubungkan ke fitur aplikasi oleh pengembang agar dapat membatasi halaman atau aksi tersebut. Kode permission bawaan dilindungi agar aturan akses yang menggunakannya tidak terputus.</p>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title">Pengaturan Permission</h3>
            <div class="ml-auto d-flex">
                <form action="{{ route('permissions.index') }}" method="GET" class="mr-2">
                    <div class="input-group input-group-sm" style="width: 240px;">
                        <input type="text" name="search" class="form-control" placeholder="Cari permission..."
                            value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button class="btn btn-default" type="submit" aria-label="Cari permission">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#create-permission">
                    <i class="fas fa-plus mr-1"></i> Tambah Permission
                </button>
            </div>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-bordered mb-0">
                <thead>
                    <tr>
                        <th style="width: 60px;">No</th>
                        <th>Permission</th>
                        <th>Penjelasan</th>
                        <th>Dipakai Role</th>
                        <th>Jenis</th>
                        <th style="width: 180px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($permissions as $permission)
                        @php($isSystem = in_array($permission->slug, $systemPermissions, true))
                        <tr>
                            <td>{{ $permissions->firstItem() + $loop->index }}</td>
                            <td>
                                <strong>{{ $permission->name }}</strong>
                                <small class="d-block text-muted">{{ $permission->slug }}</small>
                            </td>
                            <td>{{ $permission->description ?: '-' }}</td>
                            <td>{{ $permission->roles_count }}</td>
                            <td>
                                <span class="badge {{ $isSystem ? 'badge-primary' : 'badge-secondary' }}">
                                    {{ $isSystem ? 'Bawaan sistem' : 'Tambahan' }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-warning btn-sm" data-toggle="modal"
                                    data-target="#edit-permission-{{ $permission->id }}">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                @if (! $isSystem)
                                    <form action="{{ route('permissions.destroy', $permission) }}" method="POST"
                                        class="d-inline" onsubmit="return confirm('Hapus permission {{ addslashes($permission->name) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus permission">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada permission.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($permissions->hasPages())
            <div class="card-footer">{{ $permissions->links() }}</div>
        @endif
    </div>

    <div class="modal fade" id="create-permission" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('permissions.store') }}" method="POST" class="modal-content permission-form">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Permission</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    @include('permissions.partials.form', ['permission' => null])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    @foreach ($permissions as $permission)
        <div class="modal fade" id="edit-permission-{{ $permission->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('permissions.update', $permission) }}" method="POST" class="modal-content">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Permission</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        @include('permissions.partials.form', ['permission' => $permission])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
@endsection

@section('script')
    <script>
        document.querySelectorAll('.permission-form').forEach(function (form) {
            const nameInput = form.querySelector('input[name="name"]');
            const slugInput = form.querySelector('.permission-slug');

            if (!nameInput || !slugInput || slugInput.value) {
                return;
            }

            nameInput.addEventListener('input', function () {
                slugInput.value = this.value
                    .toLowerCase()
                    .trim()
                    .replace(/[^a-z0-9]+/g, '.')
                    .replace(/^\.+|\.+$/g, '');
            });
        });
    </script>
@endsection
