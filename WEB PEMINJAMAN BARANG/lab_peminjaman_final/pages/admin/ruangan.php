<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['act'] === 'add') {
    $nama   = $conn->real_escape_string(trim($_POST['namaRuangan']));
    $sn     = (int)$_POST['SN'];
    $status = $conn->real_escape_string($_POST['status_ruangan']);
    $g_val  = 'NULL';
    if (!empty($_FILES['gambar']['name'])) {
        $g = uploadFile($_FILES['gambar'], 'ruangan');
        if ($g) $g_val = "'" . $conn->real_escape_string($g) . "'";
    }
    $ok = $conn->query("INSERT INTO ruangan (namaRuangan,SN,gambar,status_ruangan) VALUES ('$nama',$sn,$g_val,'$status')");
    alert($ok ? 'Ruangan berhasil ditambahkan.' : 'Gagal: Kode ruangan sudah digunakan.', $ok ? 'success' : 'danger');
    redirect('pages/admin/ruangan.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['act'] === 'edit') {
    $id     = (int)$_POST['id'];
    $nama   = $conn->real_escape_string(trim($_POST['namaRuangan']));
    $sn     = (int)$_POST['SN'];
    $status = $conn->real_escape_string($_POST['status_ruangan']);
    $setG   = '';
    if (!empty($_FILES['gambar']['name'])) {
        $g = uploadFile($_FILES['gambar'], 'ruangan');
        if ($g) $setG = ", gambar='" . $conn->real_escape_string($g) . "'";
    }
    $conn->query("UPDATE ruangan SET namaRuangan='$nama',SN=$sn,status_ruangan='$status'$setG WHERE id=$id");
    alert('Ruangan berhasil diperbarui.');
    redirect('pages/admin/ruangan.php');
}

if (isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    $ok = @$conn->query("DELETE FROM ruangan WHERE id=$id");
    alert($ok ? 'Ruangan dihapus.' : 'Gagal: ruangan sedang digunakan dalam peminjaman.', $ok ? 'warning' : 'danger');
    redirect('pages/admin/ruangan.php');
}

$q = trim($_GET['q'] ?? '');
$where = '';
if ($q) {
    $esc = $conn->real_escape_string($q);
    $where = "WHERE namaRuangan LIKE '%$esc%' OR SN LIKE '%$esc%' OR status_ruangan LIKE '%$esc%'";
}

$list = $conn->query("SELECT * FROM ruangan $where ORDER BY namaRuangan");
$pageTitle = 'Data Ruangan';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <form method="GET" class="flex-grow-1">
    <div class="input-group input-group-sm" style="max-width:420px;">
      <input type="search" name="q" class="form-control" placeholder="Cari data ruangan..."
        value="<?= sanitize($q) ?>">
      <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
    </div>
  </form>
  <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAdd">
    <i class="bi bi-plus-lg me-1"></i>Tambah Ruangan
  </button>
</div>

<div class="card">
<div class="card-body p-0">
<div class="table-responsive">
<table class="table table-hover mb-0">
  <thead>
    <tr><th>#</th><th>Gambar</th><th>Nama Ruangan</th><th>Kode (SN)</th><th>Status</th><th>Aksi</th></tr>
  </thead>
  <tbody>
  <?php $i = 1; while ($r = $list->fetch_assoc()): ?>
  <tr>
    <td><?= $i++ ?></td>
    <td>
      <?php if ($r['gambar']): ?>
        <img src="<?= UPLOAD_URL . $r['gambar'] ?>" class="thumb" alt="">
      <?php else: ?><span class="text-muted">—</span><?php endif; ?>
    </td>
    <td class="fw-semibold"><?= sanitize($r['namaRuangan']) ?></td>
    <td><code><?= $r['SN'] ?></code></td>
    <td><?= badgeStatus($r['status_ruangan']) ?></td>
    <td>
      <button class="btn btn-sm btn-warning py-0" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $r['id'] ?>">
        <i class="bi bi-pencil"></i>
      </button>
      <a href="?del=<?= $r['id'] ?>" class="btn btn-sm btn-danger py-0"
         data-confirm="Hapus ruangan <?= sanitize($r['namaRuangan']) ?>?">
        <i class="bi bi-trash"></i>
      </a>

      <div class="modal fade" id="modalEdit<?= $r['id'] ?>" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Edit Ruangan</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
              <input type="hidden" name="act" value="edit">
              <input type="hidden" name="id" value="<?= $r['id'] ?>">
              <div class="modal-body">
                <div class="mb-3">
                  <label class="form-label">Nama Ruangan</label>
                  <input type="text" name="namaRuangan" class="form-control"
                    value="<?= sanitize($r['namaRuangan']) ?>" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Kode Ruangan</label>
                  <input type="number" name="SN" class="form-control" value="<?= $r['SN'] ?>" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Status</label>
                  <select name="status_ruangan" class="form-select">
                    <?php foreach (['Tersedia','Dipakai','Rusak'] as $s): ?>
                    <option <?= $r['status_ruangan']===$s?'selected':'' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="mb-3">
                  <label class="form-label">Ganti Gambar <small class="text-muted">(opsional)</small></label>
                  <?php if ($r['gambar']): ?>
                    <div class="mb-1"><img src="<?= UPLOAD_URL.$r['gambar'] ?>" class="thumb"></div>
                  <?php endif; ?>
                  <input type="file" name="gambar" class="form-control" accept="image/*">
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
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
        <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Tambah Ruangan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="act" value="add">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nama Ruangan <span class="text-danger">*</span></label>
            <input type="text" name="namaRuangan" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Kode Ruangan <span class="text-danger">*</span></label>
            <input type="number" name="SN" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status_ruangan" class="form-select">
              <option selected>Tersedia</option>
              <option>Dipakai</option>
              <option>Rusak</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Gambar <small class="text-muted">(opsional)</small></label>
            <input type="file" name="gambar" class="form-control" accept="image/*">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i>Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
