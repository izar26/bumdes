<h3>Edit Data Diri Pengguna</h3>
<form action="{{ route('admin.manajemen-data.user.update', $user->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="form-group">
        <label for="name">Nama Lengkap</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
    </div>
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
    </div>
    <div class="form-group">
        <label for="username">Username (Opsional)</label>
        <input type="text" name="username" class="form-control" value="{{ old('username', $user->username) }}">
    </div>
    <div class="form-group">
        <label for="password">Password Baru (kosongkan jika tidak ingin diubah)</label>
        <input type="password" name="password" class="form-control">
    </div>
    <div class="form-group">
        <label for="password_confirmation">Konfirmasi Password Baru</label>
        <input type="password" name="password_confirmation" class="form-control">
    </div>

    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
</form>
