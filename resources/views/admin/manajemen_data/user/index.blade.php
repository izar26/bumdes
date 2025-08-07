<h3>Daftar Anggota dan Manajemen Jabatan</h3>
<a href="{{ route('admin.manajemen-data.user.create') }}" class="btn btn-primary mb-3">Tambah Anggota Baru</a>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<table class="table table-bordered">
    <thead>
        <tr>
            <th>No.</th>
            <th>Nama</th>
            <th>Email</th>
            <th>Jabatan</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $user)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>
                <form action="{{ route('admin.manajemen-data.user.updateRole', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="input-group">
                        <select name="role" class="form-control">
                            @foreach($rolesOptions as $role)
                                <option value="{{ $role }}" @if($user->hasRole($role)) selected @endif>
                                    {{ Str::title(str_replace('_', ' ', $role)) }}
                                </option>
                            @endforeach
                        </select>
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-success">Ubah</button>
                        </div>
                    </div>
                </form>
            </td>
            <td>
                <a href="{{ route('admin.manajemen-data.user.edit', $user->id) }}" class="btn btn-sm btn-warning">Edit Data Diri</a>
                </td>
        </tr>
        @endforeach
    </tbody>
</table>
