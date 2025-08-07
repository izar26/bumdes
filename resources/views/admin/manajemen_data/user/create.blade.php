<h3>Buat Akun Pengguna Baru</h3>
<form action="{{ route('admin.manajemen-data.user.store') }}" method="POST">
    @csrf
    <div class="form-group">
        <label for="name">Nama Lengkap</label>
        <input type="text" name="name" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="username">Username (Opsional)</label>
        <input type="text" name="username" class="form-control">
    </div>
    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" name="password" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="password_confirmation">Konfirmasi Password</label>
        <input type="password" name="password_confirmation" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary">Buat Akun</button>
</form>
