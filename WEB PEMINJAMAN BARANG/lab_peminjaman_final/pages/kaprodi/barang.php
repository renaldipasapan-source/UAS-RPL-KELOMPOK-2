<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('kaprodi');

$list = $conn->query("SELECT b.*, t.nama AS tipe FROM Barang b LEFT JOIN TypeBarang t ON t.id=b.id_type ORDER BY b.namaBarang");
$pageTitle = 'Inventaris Barang';
require_once __DIR__ . '/../../includes/header.php';
?>

<h4 class="fw-bold mb-4"><i class="bi bi-box-seam me-2 text-primary"></i>Inventaris Barang</h4>

<div class="card">
<div class="card-body p-0">
<div class="table-responsive">
<table class="table table-hover mb-0">
  <thead><tr><th>#</th><th>Gambar</th><th>Nama Barang</th><th>Serial Number</th><th>Tipe</th><th>Qty</th><th>Status</th></tr></thead>
  <tbody>
  <?php $i=1; while ($r = $list->fetch_assoc()): ?>
  <tr>
    <td><?= $i++ ?></td>
    <td><?= $r['gambar'] ? '<img src="'.UPLOAD_URL.$r['gambar'].'" class="thumb">' : '<span class="text-muted">-</span>' ?></td>
    <td><?= sanitize($r['namaBarang']) ?></td>
    <td><code><?= sanitize($r['SN']) ?></code></td>
    <td><?= sanitize($r['tipe'] ?? '-') ?></td>
    <td><?= $r['qty'] ?></td>
    <td><?= badgeStatus($r['status_barang']) ?></td>
  </tr>
  <?php endwhile; ?>
  </tbody>
</table>
</div></div></div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
