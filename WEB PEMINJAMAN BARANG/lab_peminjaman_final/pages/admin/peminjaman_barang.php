<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');

// ─── Serah Terima (admin tetap bisa melakukan serah terima) ───
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['serahterima'])) {
    $id      = (int)$_POST['id'];
    $ket     = $conn->real_escape_string(trim($_POST['keterangan_serahterima']));
    $kondisi = $conn->real_escape_string($_POST['status_kondisi']);
    $tgl_st  = $conn->real_escape_string($_POST['tgl_serahterima']);
    $tgl_fix = $conn->real_escape_string($_POST['tgl_fix']);
    $foto_val = 'NULL';
    if (!empty($_FILES['foto_serahterima']['name'])) {
        $f = uploadFile($_FILES['foto_serahterima'], 'serahterima');
        if ($f) $foto_val = "'" . $conn->real_escape_string($f) . "'";
    }
    $conn->query("UPDATE form_peminjamanBarang SET
        keterangan_serahterima='$ket', status_kondisi='$kondisi',
        tgl_serahterima='$tgl_st', tgl_fix='$tgl_fix',
        foto_serahterima=$foto_val WHERE id=$id");
    // Kembalikan stok
    $rec = $conn->query("SELECT id_barang, qty FROM form_peminjamanBarang WHERE id=$id")->fetch_assoc();
    $conn->query("UPDATE Barang SET qty = qty + {$rec['qty']},
        status_barang = IF(qty + {$rec['qty']} > 0, 'Tersedia', status_barang)
        WHERE id = {$rec['id_barang']}");
    if ($kondisi === 'Rusak Berat') {
        $conn->query("UPDATE Barang SET status_barang='Rusak' WHERE id={$rec['id_barang']}");
    }
    alert('Serah terima berhasil dicatat.');
    redirect('pages/admin/peminjaman_barang.php');
}

$filter = $_GET['status'] ?? '';
$q      = trim($_GET['q'] ?? '');
$where_parts = [];
if ($filter) {
  $where_parts[] = "fpb.status_approval = '$filter'";
}
if ($q) {
  $esc = $conn->real_escape_string($q);
  $where_parts[] = "(
    fpb.nama LIKE '%$esc%' OR fpb.nomor_identitas LIKE '%$esc%' OR fpb.jenis_identitas LIKE '%$esc%' OR
    fpb.phone LIKE '%$esc%' OR b.namaBarang LIKE '%$esc%' OR b.SN LIKE '%$esc%' OR
    fpb.keterangan LIKE '%$esc%' OR fpb.status_approval LIKE '%$esc%'
  )";
}
$where = $where_parts ? 'WHERE ' . implode(' AND ', $where_parts) : '';
$list   = $conn->query("
  SELECT fpb.*, b.namaBarang, b.SN AS snBarang FROM form_peminjamanBarang fpb
  JOIN Barang b ON b.id = fpb.id_barang
  $where ORDER BY fpb.created_at DESC");

$pageTitle = 'Kelola Peminjaman Barang';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <form method="GET" class="d-flex align-items-center gap-2 flex-grow-1">
    <input type="hidden" name="status" value="<?= sanitize($filter) ?>">
    <div class="input-group input-group-sm w-100" style="max-width:420px;">
      <input type="search" name="q" class="form-control" placeholder="Cari peminjaman..."
        value="<?= sanitize($q) ?>">
      <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
    </div>
  </form>
  <div class="d-flex gap-2 flex-wrap">
    <div class="fw-bold text-muted align-self-center">Filter:</div>
    <?php foreach ([''=>'Semua','Waiting'=>'Waiting','Approved'=>'Approved','Deny'=>'Deny'] as $k=>$v): ?>
    <a href="?status=<?= $k ?>&q=<?= urlencode($q) ?>"
       class="btn btn-sm <?= $filter === $k ? 'btn-primary' : 'btn-outline-secondary' ?>">
      <?= $v ?>
    </a>
    <?php endforeach; ?>
  </div>
</div>

<div class="card">
<div class="card-body p-0">
<div class="table-responsive">
<table class="table table-hover mb-0">
  <thead>
    <tr>
      <th>#</th><th>Peminjam</th><th>Barang</th><th>Qty</th>
      <th>Tgl Pinjam</th><th>Tgl Kembali</th><th>Keterangan</th>
      <th>Bukti</th><th>Status</th><th>Aksi</th>
    </tr>
  </thead>
  <tbody>
  <?php $i = 1; while ($r = $list->fetch_assoc()): ?>
  <tr>
    <td><?= $i++ ?></td>
    <td>
      <div class="fw-semibold"><?= sanitize($r['nama']) ?></div>
      <small class="text-muted"><?= sanitize($r['jenis_identitas']) ?>: <?= sanitize($r['nomor_identitas']) ?></small><br>
      <small class="text-muted"><i class="bi bi-telephone me-1"></i><?= sanitize($r['phone']) ?></small>
    </td>
    <td>
      <div><?= sanitize($r['namaBarang']) ?></div>
      <small class="text-muted"><code><?= sanitize($r['snBarang']) ?></code></small>
    </td>
    <td><?= $r['qty'] ?></td>
    <td><?= $r['tgl_pinjam'] ?></td>
    <td><?= $r['tgl_kembali'] ?></td>
    <td><small><?= sanitize(mb_strimwidth($r['keterangan'], 0, 50, '...')) ?></small></td>
    <td>
      <?php if ($r['buktiFoto']): ?>
        <a href="<?= UPLOAD_URL . $r['buktiFoto'] ?>" target="_blank">
          <img src="<?= UPLOAD_URL . $r['buktiFoto'] ?>" class="thumb" alt="foto">
        </a>
      <?php else: ?><span class="text-muted">-</span><?php endif; ?>
    </td>
    <td><?= badgeApproval($r['status_approval']) ?></td>
    <td>
      <?php if ($r['status_approval'] === 'Approved' && !$r['tgl_serahterima']): ?>
        <button class="btn btn-sm btn-info py-0 px-2"
          data-bs-toggle="modal" data-bs-target="#modalST<?= $r['id'] ?>">
          <i class="bi bi-arrow-return-left me-1"></i>Serah Terima
        </button>

        <!-- Modal Serah Terima -->
        <div class="modal fade" id="modalST<?= $r['id'] ?>" tabindex="-1">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header bg-info bg-opacity-10">
                <h5 class="modal-title">
                  <i class="bi bi-arrow-return-left me-2"></i>
                  Serah Terima — <?= sanitize($r['namaBarang']) ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="serahterima" value="1">
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <div class="modal-body">
                  <div class="row g-3">
                    <div class="col-12">
                      <label class="form-label fw-semibold">Keterangan Serah Terima</label>
                      <textarea name="keterangan_serahterima" class="form-control" rows="3"
                        placeholder="Catatan kondisi saat pengembalian..."></textarea>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label fw-semibold">Kondisi Barang</label>
                      <select name="status_kondisi" class="form-select">
                        <option value="Baik">Baik</option>
                        <option value="Rusak Ringan">Rusak Ringan</option>
                        <option value="Rusak Berat">Rusak Berat</option>
                      </select>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label fw-semibold">Tgl Serah Terima</label>
                      <input type="date" name="tgl_serahterima" class="form-control"
                        value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-4">
                      <label class="form-label fw-semibold">Tgl Kembali Aktual</label>
                      <input type="date" name="tgl_fix" class="form-control"
                        value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-12">
                      <label class="form-label fw-semibold">Foto Serah Terima</label>
                      <input type="file" name="foto_serahterima" class="form-control" accept="image/*">
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                  <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i>Simpan Serah Terima
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>

      <?php elseif ($r['tgl_serahterima']): ?>
        <span class="badge bg-secondary">
          <i class="bi bi-check-circle me-1"></i>Selesai<br>
          <small><?= $r['tgl_serahterima'] ?></small>
        </span>
      <?php elseif ($r['status_approval'] === 'Waiting'): ?>
        <span class="text-muted fst-italic"><small>Menunggu approval Kaprodi</small></span>
      <?php else: ?>
        <span class="text-muted">-</span>
      <?php endif; ?>
    </td>
  </tr>
  <?php endwhile; ?>
  </tbody>
</table>
</div>
</div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
