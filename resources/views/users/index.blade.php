@extends('layouts.app')

@section('content')
    @php
        $canEditUsers = auth()->user()->hasPermission('users.update');
        $isSuperAdmin = auth()->user()->hasRole('super-admin');
    @endphp
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title">Manajemen User</h3>
            <div class="ml-auto d-flex">
                <form action="{{ route('users.index') }}" method="GET" class="mr-2">
                    <div class="input-group input-group-sm" style="width: 240px;">
                        <input type="text" name="search" class="form-control" placeholder="Cari user..."
                            value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button class="btn btn-default" type="submit"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                </form>
                @if (auth()->user()->hasPermission('users.create'))
                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#create-user">
                        <i class="fas fa-plus mr-1"></i> Tambah User
                    </button>
                @endif
            </div>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-bordered mb-0">
                <thead>
                    <tr>
                        <th style="width: 60px;">No</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th style="width: 180px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        @php($isProtectedAccount = ! $isSuperAdmin && $user->roles->contains('slug', 'super-admin'))
                        <tr>
                            <td>{{ $users->firstItem() + $loop->index }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->username }}</td>
                            <td>
                                @forelse ($user->roles as $role)
                                    <span class="badge badge-info">{{ $role->name }}</span>
                                @empty
                                    <span class="text-muted">Tanpa role</span>
                                @endforelse
                            </td>
                            <td>
                                <span class="badge {{ $user->status === 'aktif' ? 'badge-success' : 'badge-secondary' }}">
                                    {{ $user->status === 'aktif' ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td>
                                @if ($canEditUsers)
                                    <button type="button" class="btn btn-warning btn-sm"
                                        @if ($isProtectedAccount)
                                            disabled title="Hanya Super Admin yang bisa mengedit akun ini"
                                        @else
                                            data-toggle="modal" data-target="#edit-user-{{ $user->id }}"
                                        @endif>
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                @endif
                                @if (auth()->user()->hasPermission('users.delete') && ! auth()->user()->is($user) && $user->status === 'aktif')
                                    @if ($isProtectedAccount)
                                        <button class="btn btn-danger btn-sm" type="button" disabled
                                            title="Hanya Super Admin yang bisa menonaktifkan akun ini" aria-label="Nonaktifkan">
                                            <i class="fas fa-user-slash" aria-hidden="true"></i>
                                        </button>
                                    @else
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Nonaktifkan user {{ addslashes($user->username) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm" type="submit">
                                            <i class="fas fa-user-slash"></i>
                                        </button>
                                    </form>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada akun user.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="card-footer">{{ $users->links() }}</div>
        @endif
    </div>

    @if (auth()->user()->hasPermission('users.create'))
    <div class="modal fade" id="create-user" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('users.store') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah User</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    @include('users.partials.form', ['user' => null])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if ($canEditUsers)
    @foreach ($users as $user)
        @continue(! $isSuperAdmin && $user->roles->contains('slug', 'super-admin'))
        <div class="modal fade" id="edit-user-{{ $user->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('users.update', $user) }}" method="POST" class="modal-content">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit User</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        @include('users.partials.form', ['user' => $user])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
    @endif
@endsection
