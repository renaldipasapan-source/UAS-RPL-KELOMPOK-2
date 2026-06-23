<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('kaprodi');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
    $id     = (int)$_POST['id'];
    $action = $conn->real_escape_string($_POST['action']);
    if (in_array($action, ['Approved', 'Deny'])) {
        $conn->query("UPDATE form_pengaduanMasalah SET status_pengaduan='$action' WHERE id=$id");
        alert($action === 'Approved' ? 'Pengaduan disetujui.' : 'Pengaduan ditolak.',
              $action === 'Approved' ? 'success' : 'warning');
    }
    redirect('pages/kaprodi/pengaduan.php');
}

$filter_tipe = $_GET['tipe'] ?? '';
$filter_status = $_GET['status'] ?? '';
$where_parts = [];
if ($filter_tipe) {
    $ft = $conn->real_escape_string($filter_tipe);
    $where_parts[] = "fpm.tipe_peminjaman = '$ft'";
}
if ($filter_status) {
    $fs = $conn->real_escape_string($filter_status);
    $where_parts[] = "fpm.status_pengaduan = '$fs'";
}
$where_sql = $where_parts ? 'WHERE ' . implode(' AND ', $where_parts) : '';

$list = $conn->query(
    "SELECT fpm.*, u.nama AS nama_pelapor,"
  . " COALESCE(b.namaBarang, r.namaRuangan) AS objek "
  . "FROM form_pengaduanMasalah fpm "
  . "JOIN users u ON u.id=fpm.id_user "
  . "LEFT JOIN form_peminjamanBarang fpb ON fpb.id=fpm.id_form_peminjamanBarang "
  . "LEFT JOIN Barang b ON b.id=fpb.id_barang "
  . "LEFT JOIN form_peminjamanRuangan fpr ON fpr.id=fpm.id_form_peminjamanRuangan "
  . "LEFT JOIN ruangan r ON r.id=fpr.id_ruangan "
  . "$where_sql "
  . "ORDER BY fpm.tingkat_prioritas DESC, fpm.created_at DESC"
);

$pageTitle = 'Approval Pengaduan';
require_once __DIR__ . '/../../includes/header.php';
?>

<h4 class="fw-bold mb-4"><i class="bi bi-exclamation-triangle me-2 text-danger"></i>Approval Pengaduan Masalah</h4>

<div class="mb-3 d-flex gap-2 flex-wrap align-items-center">
  <span class="text-muted small me-1">Tipe:</span>
  <?php foreach ([''=>'Semua','Barang'=>'Barang','Ruangan'=>'Ruangan'] as $k=>$v): ?>
  <a href="?tipe=<?= $k ?>&status=<?= $filter_status ?>"
     class="btn btn-sm <?= $filter_tipe === $k ? 'btn-primary' : 'btn-outline-secondary' ?>">
    <?= $v ?>
  </a>
  <?php endforeach; ?>

  <span class="text-muted small ms-3 me-1">Status:</span>
  <?php foreach ([''=>'Semua','Waiting'=>'Waiting','Approved'=>'Approved','Deny'=>'Deny'] as $k=>$v): ?>
  <a href="?tipe=<?= $filter_tipe ?>&status=<?= $k ?>"
     class="btn btn-sm <?= $filter_status === $k ? 'btn-secondary' : 'btn-outline-secondary' ?>">
    <?= $v ?>
  </a>
  <?php endforeach; ?>
</div>
<div class="card">
<div class="card-body p-0">
<div class="table-responsive">
<table class="table table-hover mb-0">
  <thead><tr><th>#</th><th>Pelapor</th><th>Tipe</th><th>Objek</th><th>Deskripsi</th><th>Prioritas</th><th>Status</th><th>Tgl</th><th>Resolusi</th><th>Aksi</th></tr></thead>
  <tbody>
  <?php $i=1; while ($r = $list->fetch_assoc()): ?>
  <tr>
    <td><?= $i++ ?></td>
    <td><?= sanitize($r['nama_pelapor']) ?></td>
    <td><?= sanitize($r['tipe_peminjaman']) ?></td>
    <td><?= sanitize($r['objek'] ?? '-') ?></td>
    <td><?= sanitize(mb_strimwidth($r['deskripsi_masalah'],0,60,'...')) ?></td>
    <td><?= priorityLabel($r['tingkat_prioritas']) ?></td>
    <td><?= badgeApproval($r['status_pengaduan']) ?></td>
    <td><?= $r['tgl_pengaduan'] ?></td>
    <td><?= $r['deskripsi_resolusi'] ? sanitize(mb_strimwidth($r['deskripsi_resolusi'],0,50,'...')) : '<span class="text-muted">-</span>' ?></td>
    <td>
      <?php if ($r['status_pengaduan'] === 'Waiting'): ?>
      <form method="POST" class="d-flex gap-1">
        <input type="hidden" name="id" value="<?= $r['id'] ?>">
        <button type="submit" name="action" value="Approved" class="btn btn-sm btn-success py-0 px-2">
          <i class="bi bi-check-lg"></i> Approve
        </button>
        <button type="submit" name="action" value="Deny" class="btn btn-sm btn-danger py-0 px-2">
          <i class="bi bi-x-lg"></i> Deny
        </button>
      </form>
      <?php else: ?>
        <span class="text-muted">-</span>
      <?php endif; ?>
    </td>
  </tr>
  <?php endwhile; ?>
  </tbody>
</table>
</div></div></div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
