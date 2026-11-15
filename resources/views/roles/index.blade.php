@extends('layouts.app')

@section('styles')
    <style>
        .permission-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .75rem;
            max-height: 280px;
            overflow-y: auto;
        }

        .permission-item {
            min-width: 0;
        }

        @media (max-width: 575.98px) {
            .permission-grid {
                grid-template-columns: minmax(0, 1fr);
            }
        }
    </style>
@endsection

@section('content')
    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-question-circle mr-1"></i> Cara Menggunakan Role & Permission</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse" aria-label="Tutup petunjuk">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <ol class="mb-2 pl-3">
                <li>Jika izin yang dibutuhkan belum tersedia, buat dahulu melalui <strong>Pengaturan Permission</strong>.</li>
                <li>Tekan <strong>Tambah Role</strong>, isi nama role, lalu centang permission yang boleh digunakan.</li>
                <li>Buka halaman <strong>Manajemen User</strong>, edit user, kemudian pilih role tersebut.</li>
                <li>Minta user masuk ulang agar aturan akses terbaru langsung digunakan.</li>
            </ol>
            <div class="alert alert-warning py-2 mb-2">
                Jangan menghapus permission yang masih dipakai role. Permission tambahan baru akan membatasi fitur setelah dihubungkan ke fitur aplikasi.
            </div>
            <a href="{{ route('permissions.index') }}" class="btn btn-info btn-sm">
                <i class="fas fa-key mr-1"></i> Buka Pengaturan Permission
            </a>
            @if (auth()->user()->hasPermission('users.view'))
                <a href="{{ route('users.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-users mr-1"></i> Buka Manajemen User
                </a>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title">Manajemen Role & Permission</h3>
            <div class="ml-auto d-flex">
                <form action="{{ route('roles.index') }}" method="GET" class="mr-2">
                    <div class="input-group input-group-sm" style="width: 240px;">
                        <input type="text" name="search" class="form-control" placeholder="Cari role..."
                            value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button class="btn btn-default" type="submit" aria-label="Cari role">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
                <a href="{{ route('permissions.index') }}" class="btn btn-info btn-sm">
                    <i class="fas fa-key mr-1"></i> Permission
                </a>
                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#create-role">
                    <i class="fas fa-plus mr-1"></i> Tambah Role
                </button>
            </div>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-bordered mb-0">
                <thead>
                    <tr>
                        <th style="width: 60px;">No</th>
                        <th>Role</th>
                        <th>Permission</th>
                        <th>User</th>
                        <th>Status</th>
                        <th style="width: 180px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $role)
                        <tr>
                            <td>{{ $roles->firstItem() + $loop->index }}</td>
                            <td>
                                <strong>{{ $role->name }}</strong>
                                <small class="d-block text-muted">{{ $role->slug }}</small>
                            </td>
                            <td>
                                @forelse ($role->permissions as $permission)
                                    <span class="badge badge-info mb-1">{{ $permission->name }}</span>
                                @empty
                                    <span class="text-muted">Tanpa permission</span>
                                @endforelse
                            </td>
                            <td>{{ $role->users_count }}</td>
                            <td>
                                <span class="badge {{ $role->is_active ? 'badge-success' : 'badge-secondary' }}">
                                    {{ $role->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-warning btn-sm" data-toggle="modal"
                                    data-target="#edit-role-{{ $role->id }}">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                @if ($role->slug !== 'super-admin')
                                    <form action="{{ route('roles.destroy', $role) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Hapus role {{ addslashes($role->name) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm" type="submit" title="Hapus role">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada role.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($roles->hasPages())
            <div class="card-footer">{{ $roles->links() }}</div>
        @endif
    </div>

    <div class="modal fade" id="create-role" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="{{ route('roles.store') }}" method="POST" class="modal-content role-form">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Role</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    @include('roles.partials.form', ['role' => null, 'formId' => 'create'])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    @foreach ($roles as $role)
        <div class="modal fade" id="edit-role-{{ $role->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form action="{{ route('roles.update', $role) }}" method="POST" class="modal-content role-form">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Role {{ $role->name }}</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        @include('roles.partials.form', ['role' => $role, 'formId' => 'edit-'.$role->id])
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
        document.querySelectorAll('.role-form').forEach(function (form) {
            const nameInput = form.querySelector('input[name="name"]');
            const slugInput = form.querySelector('.role-slug:not([readonly])');

            if (!nameInput || !slugInput || slugInput.value) {
                return;
            }

            nameInput.addEventListener('input', function () {
                slugInput.value = this.value
                    .toLowerCase()
                    .trim()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            });
        });
    </script>
@endsection
