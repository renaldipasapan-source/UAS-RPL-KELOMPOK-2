<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');

// ─── Serah Terima (admin tetap bisa melakukan serah terima) ───
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['serahterima'])) {
    $id      = (int)$_POST['id'];
    $ket     = $conn->real_escape_string(trim($_POST['keterangan_serahterima']));
    $kondisi = $conn->real_escape_string($_POST['status_kondisi']);
    $tgl     = $conn->real_escape_string($_POST['tgl_serahterima']);
    $foto_val = 'NULL';
    if (!empty($_FILES['foto_serahterima']['name'])) {
        $f = uploadFile($_FILES['foto_serahterima'], 'serahterima');
        if ($f) $foto_val = "'" . $conn->real_escape_string($f) . "'";
    }
    $conn->query("UPDATE form_peminjamanRuangan SET
        keterangan_serahterima='$ket', status_kondisi='$kondisi',
        tgl_serahterima='$tgl', foto_serahterima=$foto_val WHERE id=$id");
    $rec = $conn->query("SELECT id_ruangan FROM form_peminjamanRuangan WHERE id=$id")->fetch_assoc();
    $newStatus = ($kondisi === 'Ada Kerusakan') ? 'Rusak' : 'Tersedia';
    $conn->query("UPDATE ruangan SET status_ruangan='$newStatus' WHERE id={$rec['id_ruangan']}");
    alert('Serah terima ruangan dicatat.');
    redirect('pages/admin/peminjaman_ruangan.php');
}

$filter = $_GET['status'] ?? '';
$q      = trim($_GET['q'] ?? '');
$where_parts = [];
if ($filter) {
  $where_parts[] = "fpr.status_approval='$filter'";
}
if ($q) {
  $esc = $conn->real_escape_string($q);
  $where_parts[] = "(
    fpr.nama LIKE '%$esc%' OR fpr.nomor_identitas LIKE '%$esc%' OR fpr.jenis_identitas LIKE '%$esc%' OR
    fpr.phone LIKE '%$esc%' OR r.namaRuangan LIKE '%$esc%' OR r.SN LIKE '%$esc%' OR
    fpr.keterangan LIKE '%$esc%' OR fpr.status_approval LIKE '%$esc%'
  )";
}
$where = $where_parts ? 'WHERE ' . implode(' AND ', $where_parts) : '';
$list   = $conn->query("
  SELECT fpr.*, r.namaRuangan, r.SN AS kodRuangan FROM form_peminjamanRuangan fpr
  JOIN ruangan r ON r.id = fpr.id_ruangan
  $where ORDER BY fpr.created_at DESC");

$pageTitle = 'Kelola Peminjaman Ruangan';
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
       class="btn btn-sm <?= $filter === $k ? 'btn-success' : 'btn-outline-secondary' ?>">
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
    <tr><th>#</th><th>Peminjam</th><th>Ruangan</th><th>Waktu Mulai</th><th>Waktu Selesai</th><th>Keterangan</th><th>Status</th><th>Aksi</th></tr>
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
      <div><?= sanitize($r['namaRuangan']) ?></div>
      <small class="text-muted">Kode: <code><?= $r['kodRuangan'] ?></code></small>
    </td>
    <td><?= date('d/m/Y H:i', strtotime($r['wkt_pinjam'])) ?></td>
    <td><?= date('d/m/Y H:i', strtotime($r['wkt_kembali'])) ?></td>
    <td><small><?= sanitize(mb_strimwidth($r['keterangan'], 0, 50, '...')) ?></small></td>
    <td><?= badgeApproval($r['status_approval']) ?></td>
    <td>
      <?php if ($r['status_approval'] === 'Approved' && !$r['tgl_serahterima']): ?>
        <button class="btn btn-sm btn-info py-0 px-2"
          data-bs-toggle="modal" data-bs-target="#modalST<?= $r['id'] ?>">
          <i class="bi bi-arrow-return-left me-1"></i>Serah Terima
        </button>

        <div class="modal fade" id="modalST<?= $r['id'] ?>" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Serah Terima Ruangan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="serahterima" value="1">
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <div class="modal-body">
                  <div class="mb-3">
                    <label class="form-label">Keterangan Serah Terima</label>
                    <textarea name="keterangan_serahterima" class="form-control" rows="3"></textarea>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Kondisi Ruangan</label>
                    <select name="status_kondisi" class="form-select">
                      <option value="Baik">Baik</option>
                      <option value="Kotor">Kotor</option>
                      <option value="Ada Kerusakan">Ada Kerusakan</option>
                    </select>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Tanggal Serah Terima</label>
                    <input type="date" name="tgl_serahterima" class="form-control" value="<?= date('Y-m-d') ?>">
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Foto Serah Terima</label>
                    <input type="file" name="foto_serahterima" class="form-control" accept="image/*">
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

      <?php elseif ($r['tgl_serahterima']): ?>
        <span class="badge bg-secondary">Selesai <?= $r['tgl_serahterima'] ?></span>
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
