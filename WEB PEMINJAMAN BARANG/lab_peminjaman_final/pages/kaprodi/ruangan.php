<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('kaprodi');

$list = $conn->query("SELECT * FROM ruangan ORDER BY namaRuangan");
$pageTitle = 'Daftar Ruangan';
require_once __DIR__ . '/../../includes/header.php';
?>

<h4 class="fw-bold mb-4"><i class="bi bi-building me-2 text-success"></i>Daftar Ruangan</h4>

<div class="card">
<div class="card-body p-0">
<div class="table-responsive">
<table class="table table-hover mb-0">
  <thead><tr><th>#</th><th>Gambar</th><th>Nama Ruangan</th><th>Kode</th><th>Status</th></tr></thead>
  <tbody>
  <?php $i=1; while ($r = $list->fetch_assoc()): ?>
  <tr>
    <td><?= $i++ ?></td>
    <td><?= $r['gambar'] ? '<img src="'.UPLOAD_URL.$r['gambar'].'" class="thumb">' : '<span class="text-muted">-</span>' ?></td>
    <td><?= sanitize($r['namaRuangan']) ?></td>
    <td><code><?= $r['SN'] ?></code></td>
    <td><?= badgeStatus($r['status_ruangan']) ?></td>
  </tr>
  <?php endwhile; ?>
  </tbody>
</table>
</div></div></div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
