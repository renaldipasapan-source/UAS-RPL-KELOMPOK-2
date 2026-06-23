<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['act'] === 'add') {
    $nama   = $conn->real_escape_string(trim($_POST['namaBarang']));
    $sn     = $conn->real_escape_string(trim($_POST['SN']));
    $id_type= (int)$_POST['id_type'];
    $qty    = (int)$_POST['qty'];
    $status = $conn->real_escape_string($_POST['status_barang']);
    $g_val  = 'NULL';
    if (!empty($_FILES['gambar']['name'])) {
        $g = uploadFile($_FILES['gambar'], 'barang');
        if ($g) $g_val = "'" . $conn->real_escape_string($g) . "'";
    }
    $ok = $conn->query("INSERT INTO Barang (namaBarang,SN,gambar,id_type,qty,status_barang)
                        VALUES ('$nama','$sn',$g_val,$id_type,$qty,'$status')");
    alert($ok ? 'Barang berhasil ditambahkan.' : 'Gagal: Serial Number sudah digunakan.', $ok ? 'success' : 'danger');
    redirect('pages/admin/barang.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['act'] === 'edit') {
    $id     = (int)$_POST['id'];
    $nama   = $conn->real_escape_string(trim($_POST['namaBarang']));
    $sn     = $conn->real_escape_string(trim($_POST['SN']));
    $id_type= (int)$_POST['id_type'];
    $qty    = (int)$_POST['qty'];
    $status = $conn->real_escape_string($_POST['status_barang']);
    $setG   = '';
    if (!empty($_FILES['gambar']['name'])) {
        $g = uploadFile($_FILES['gambar'], 'barang');
        if ($g) $setG = ", gambar='" . $conn->real_escape_string($g) . "'";
    }
    $conn->query("UPDATE Barang SET namaBarang='$nama',SN='$sn',id_type=$id_type,qty=$qty,status_barang='$status'$setG WHERE id=$id");
    alert('Barang berhasil diperbarui.');
    redirect('pages/admin/barang.php');
}

if (isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    $ok = @$conn->query("DELETE FROM Barang WHERE id=$id");
    alert($ok ? 'Barang dihapus.' : 'Gagal menghapus: barang sedang digunakan dalam peminjaman.', $ok ? 'warning' : 'danger');
    redirect('pages/admin/barang.php');
}

$q = trim($_GET['q'] ?? '');
$where = '';
if ($q) {
    $esc = $conn->real_escape_string($q);
    $where = "WHERE b.namaBarang LIKE '%$esc%' OR b.SN LIKE '%$esc%' OR t.nama LIKE '%$esc%' OR b.qty LIKE '%$esc%' OR b.status_barang LIKE '%$esc%'";
}

$barangList = $conn->query("SELECT b.*, t.nama AS tipe FROM Barang b LEFT JOIN TypeBarang t ON t.id=b.id_type $where ORDER BY b.namaBarang");
$typeList   = $conn->query("SELECT * FROM TypeBarang ORDER BY nama");
$types = [];
while ($t = $typeList->fetch_assoc()) $types[] = $t;

$pageTitle = 'Data Barang';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <form method="GET" class="flex-grow-1">
    <div class="input-group input-group-sm" style="max-width:420px;">
      <input type="search" name="q" class="form-control" placeholder="Cari data barang..."
        value="<?= sanitize($q) ?>">
      <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
    </div>
  </form>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAdd">
    <i class="bi bi-plus-lg me-1"></i>Tambah Barang
  </button>
</div>

<div class="card">
<div class="card-body p-0">
<div class="table-responsive">
<table class="table table-hover mb-0">
  <thead>
    <tr><th>#</th><th>Gambar</th><th>Nama Barang</th><th>Serial Number</th><th>Tipe</th><th>Qty</th><th>Status</th><th>Aksi</th></tr>
  </thead>
  <tbody>
  <?php $i = 1; while ($r = $barangList->fetch_assoc()): ?>
  <tr>
    <td><?= $i++ ?></td>
    <td>
      <?php if ($r['gambar']): ?>
        <img src="<?= UPLOAD_URL . $r['gambar'] ?>" class="thumb" alt="">
      <?php else: ?><span class="text-muted">—</span><?php endif; ?>
    </td>
    <td class="fw-semibold"><?= sanitize($r['namaBarang']) ?></td>
    <td><code><?= sanitize($r['SN']) ?></code></td>
    <td><?= sanitize($r['tipe'] ?? '-') ?></td>
    <td><?= $r['qty'] ?></td>
    <td><?= badgeStatus($r['status_barang']) ?></td>
    <td>
      <button class="btn btn-sm btn-warning py-0" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $r['id'] ?>">
        <i class="bi bi-pencil"></i>
      </button>
      <a href="?del=<?= $r['id'] ?>" class="btn btn-sm btn-danger py-0"
         data-confirm="Hapus barang <?= sanitize($r['namaBarang']) ?>?">
        <i class="bi bi-trash"></i>
      </a>

      <!-- Modal Edit -->
      <div class="modal fade" id="modalEdit<?= $r['id'] ?>" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Edit Barang</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
              <input type="hidden" name="act" value="edit">
              <input type="hidden" name="id" value="<?= $r['id'] ?>">
              <div class="modal-body">
                <div class="mb-3">
                  <label class="form-label">Nama Barang</label>
                  <input type="text" name="namaBarang" class="form-control"
                    value="<?= sanitize($r['namaBarang']) ?>" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Serial Number</label>
                  <input type="text" name="SN" class="form-control"
                    value="<?= sanitize($r['SN']) ?>" required>
                </div>
                <div class="row g-2 mb-3">
                  <div class="col">
                    <label class="form-label">Tipe</label>
                    <select name="id_type" class="form-select">
                      <?php foreach ($types as $t): ?>
                      <option value="<?= $t['id'] ?>" <?= $r['id_type']==$t['id']?'selected':'' ?>>
                        <?= sanitize($t['nama']) ?>
                      </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col">
                    <label class="form-label">Qty</label>
                    <input type="number" name="qty" class="form-control" min="0" value="<?= $r['qty'] ?>">
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label">Status</label>
                  <select name="status_barang" class="form-select">
                    <?php foreach (['Tersedia','Dipakai','Rusak'] as $s): ?>
                    <option <?= $r['status_barang']===$s?'selected':'' ?>><?= $s ?></option>
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

<!-- Modal Tambah Barang -->
<div class="modal fade" id="modalAdd" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Tambah Barang</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="act" value="add">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nama Barang <span class="text-danger">*</span></label>
            <input type="text" name="namaBarang" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Serial Number <span class="text-danger">*</span></label>
            <input type="text" name="SN" class="form-control" required>
          </div>
          <div class="row g-2 mb-3">
            <div class="col">
              <label class="form-label">Tipe</label>
              <select name="id_type" class="form-select">
                <?php foreach ($types as $t): ?>
                <option value="<?= $t['id'] ?>"><?= sanitize($t['nama']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col">
              <label class="form-label">Qty <span class="text-danger">*</span></label>
              <input type="number" name="qty" class="form-control" min="1" value="1" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status_barang" class="form-select">
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
          <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
