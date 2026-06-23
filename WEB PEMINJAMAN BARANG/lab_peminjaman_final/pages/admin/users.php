<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['act'] === 'add') {
    $nama  = $conn->real_escape_string(trim($_POST['nama']));
    $jenis = $conn->real_escape_string($_POST['jenis_identitas']);
    $nomor = $conn->real_escape_string(trim($_POST['nomor_identitas']));
    $role  = $conn->real_escape_string($_POST['role']);
    $pw_val = 'NULL';
    if (!empty($_POST['password'])) {
        $hash   = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $pw_val = "'" . $conn->real_escape_string($hash) . "'";
    }
    $ok = $conn->query("INSERT INTO users (nama,jenis_identitas,nomor_identitas,password,role)
                        VALUES ('$nama','$jenis','$nomor',$pw_val,'$role')");
    alert($ok ? 'Pengguna berhasil ditambahkan.' : 'Nomor identitas sudah digunakan.', $ok ? 'success' : 'danger');
    redirect('pages/admin/users.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['act'] === 'edit') {
    $id    = (int)$_POST['id'];
    $nama  = $conn->real_escape_string(trim($_POST['nama']));
    $jenis = $conn->real_escape_string($_POST['jenis_identitas']);
    $nomor = $conn->real_escape_string(trim($_POST['nomor_identitas']));
    $role  = $conn->real_escape_string($_POST['role']);
    $setPw = '';
    if (!empty($_POST['password'])) {
        $hash  = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $setPw = ", password='" . $conn->real_escape_string($hash) . "'";
    }
    $conn->query("UPDATE users SET nama='$nama',jenis_identitas='$jenis',nomor_identitas='$nomor',role='$role'$setPw WHERE id=$id");
    alert('Data pengguna diperbarui.');
    redirect('pages/admin/users.php');
}

if (isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    if ($id == getUserId()) {
        alert('Tidak dapat menghapus akun sendiri.', 'danger');
    } else {
    // Periksa apakah user memiliki data terkait di tabel peminjaman atau pengaduan
    $cntBarang = (int)($conn->query("SELECT COUNT(*) AS c FROM form_peminjamanBarang WHERE id_user=$id")->fetch_assoc()['c'] ?? 0);
    $cntRuangan = (int)($conn->query("SELECT COUNT(*) AS c FROM form_peminjamanRuangan WHERE id_user=$id")->fetch_assoc()['c'] ?? 0);
    $cntPengaduan = (int)($conn->query("SELECT COUNT(*) AS c FROM form_pengaduanMasalah WHERE id_user=$id")->fetch_assoc()['c'] ?? 0);

    $totalRelated = $cntBarang + $cntRuangan + $cntPengaduan;
    if ($totalRelated > 0) {
      $parts = [];
      if ($cntBarang) $parts[] = "$cntBarang peminjaman barang";
      if ($cntRuangan) $parts[] = "$cntRuangan peminjaman ruangan";
      if ($cntPengaduan) $parts[] = "$cntPengaduan pengaduan";
      $detail = implode(', ', $parts);
      alert("Gagal menghapus: pengguna memiliki data terkait ($detail). Hapus/transfer data terkait terlebih dahulu.", 'danger');
    } else {
      $ok = $conn->query("DELETE FROM users WHERE id=$id");
      alert($ok ? 'Pengguna dihapus.' : 'Terjadi kesalahan saat menghapus pengguna.', $ok ? 'warning' : 'danger');
    }
    }
    redirect('pages/admin/users.php');
}

$q = trim($_GET['q'] ?? '');
$where = '';
if ($q) {
    $esc = $conn->real_escape_string($q);
    $where = "WHERE nama LIKE '%$esc%' OR jenis_identitas LIKE '%$esc%' OR nomor_identitas LIKE '%$esc%' OR role LIKE '%$esc%'";
}

$list = $conn->query("SELECT * FROM users $where ORDER BY FIELD(role,'kaprodi','admin','peminjam'), nama");
$pageTitle = 'Data Pengguna';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <form method="GET" class="flex-grow-1">
    <div class="input-group input-group-sm" style="max-width:420px;">
      <input type="search" name="q" class="form-control" placeholder="Cari pengguna..."
        value="<?= sanitize($q) ?>">
      <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
    </div>
  </form>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAdd">
    <i class="bi bi-person-plus me-1"></i>Tambah Pengguna
  </button>
</div>

<div class="card">
<div class="card-body p-0">
<div class="table-responsive">
<table class="table table-hover mb-0">
  <thead>
    <tr><th>#</th><th>Nama</th><th>Jenis Identitas</th><th>Nomor Identitas</th><th>Role</th><th>Password</th><th>Terdaftar</th><th>Aksi</th></tr>
  </thead>
  <tbody>
  <?php $i = 1; while ($r = $list->fetch_assoc()): ?>
  <tr>
    <td><?= $i++ ?></td>
    <td class="fw-semibold"><?= sanitize($r['nama']) ?></td>
    <td><?= sanitize($r['jenis_identitas']) ?></td>
    <td><code><?= sanitize($r['nomor_identitas']) ?></code></td>
    <td>
      <?php
        $roleColors = ['kaprodi'=>'danger','admin'=>'warning text-dark','peminjam'=>'info text-dark'];
        $c = $roleColors[$r['role']] ?? 'secondary';
        echo '<span class="badge bg-'.$c.'">'.ucfirst($r['role']).'</span>';
      ?>
    </td>
    <td>
      <?php if ($r['password']): ?>
        <span class="badge bg-success"><i class="bi bi-lock-fill me-1"></i>Ada</span>
      <?php else: ?>
        <span class="badge bg-light text-muted border">Tidak ada</span>
      <?php endif; ?>
    </td>
    <td><?= date('d/m/Y', strtotime($r['created_at'])) ?></td>
    <td>
      <button class="btn btn-sm btn-warning py-0" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $r['id'] ?>">
        <i class="bi bi-pencil"></i>
      </button>
      <?php if ($r['id'] != getUserId()): ?>
      <a href="?del=<?= $r['id'] ?>" class="btn btn-sm btn-danger py-0"
         data-confirm="Hapus pengguna <?= sanitize($r['nama']) ?>?">
        <i class="bi bi-trash"></i>
      </a>
      <?php endif; ?>

      <!-- Modal Edit -->
      <div class="modal fade" id="modalEdit<?= $r['id'] ?>" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Edit Pengguna</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
              <input type="hidden" name="act" value="edit">
              <input type="hidden" name="id" value="<?= $r['id'] ?>">
              <div class="modal-body">
                <div class="mb-3">
                  <label class="form-label">Nama Lengkap</label>
                  <input type="text" name="nama" class="form-control"
                    value="<?= sanitize($r['nama']) ?>" required>
                </div>
                <div class="row g-2 mb-3">
                  <div class="col-md-4">
                    <label class="form-label">Jenis Identitas</label>
                    <select name="jenis_identitas" class="form-select">
                      <?php foreach (['NIM','NIP','NIK'] as $j): ?>
                      <option <?= $r['jenis_identitas']===$j?'selected':'' ?>><?= $j ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-md-8">
                    <label class="form-label">Nomor Identitas</label>
                    <input type="text" name="nomor_identitas" class="form-control"
                      value="<?= sanitize($r['nomor_identitas']) ?>" required>
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label">Role</label>
                  <select name="role" class="form-select">
                    <?php foreach (['peminjam','admin','kaprodi'] as $ro): ?>
                    <option value="<?= $ro ?>" <?= $r['role']===$ro?'selected':'' ?>>
                      <?= ucfirst($ro) ?>
                    </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="mb-3">
                  <label class="form-label">Password Baru
                    <small class="text-muted">(kosongkan jika tidak diubah)</small>
                  </label>
                  <input type="password" name="password" class="form-control"
                    placeholder="Isi untuk mengganti password">
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </td>
  </tr>
  <?php endwhile; ?>
  </tbody>
</table>
</div>
</div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalAdd" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Tambah Pengguna</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <input type="hidden" name="act" value="add">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
            <input type="text" name="nama" class="form-control" required>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-md-4">
              <label class="form-label">Jenis Identitas</label>
              <select name="jenis_identitas" class="form-select">
                <option>NIM</option><option>NIP</option><option>NIK</option>
              </select>
            </div>
            <div class="col-md-8">
              <label class="form-label">Nomor Identitas <span class="text-danger">*</span></label>
              <input type="text" name="nomor_identitas" class="form-control" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role" class="form-select">
              <option value="peminjam">Peminjam (tanpa password)</option>
              <option value="admin">Admin</option>
              <option value="kaprodi">Kaprodi</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Password
              <small class="text-muted">(wajib untuk Admin/Kaprodi)</small>
            </label>
            <input type="password" name="password" class="form-control"
              placeholder="Kosongkan untuk role Peminjam">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
