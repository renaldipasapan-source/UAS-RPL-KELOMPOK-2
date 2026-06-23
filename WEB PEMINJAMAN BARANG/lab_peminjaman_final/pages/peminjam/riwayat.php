<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('peminjam');

$uid = getUserId();
$tab = $_GET['tab'] ?? 'barang';

$barangList = $conn->query("
    SELECT fpb.*, b.namaBarang, b.SN FROM form_peminjamanBarang fpb
    JOIN Barang b ON b.id = fpb.id_barang
    WHERE fpb.id_user = $uid
    ORDER BY fpb.created_at DESC");

$ruanganList = $conn->query("
    SELECT fpr.*, r.namaRuangan, r.SN AS kodRuangan FROM form_peminjamanRuangan fpr
    JOIN ruangan r ON r.id = fpr.id_ruangan
    WHERE fpr.id_user = $uid
    ORDER BY fpr.created_at DESC");

$pageTitle = 'Riwayat Peminjaman';
require_once __DIR__ . '/../../includes/header.php';
?>

<h4 class="mb-4 fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i>Riwayat Peminjaman Saya</h4>

<ul class="nav nav-tabs mb-3">
  <li class="nav-item">
    <a class="nav-link <?= $tab === 'barang' ? 'active' : '' ?>" href="?tab=barang">
      <i class="bi bi-box me-1"></i>Barang
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?= $tab === 'ruangan' ? 'active' : '' ?>" href="?tab=ruangan">
      <i class="bi bi-door-open me-1"></i>Ruangan
    </a>
  </li>
</ul>

<?php if ($tab === 'barang'): ?>
<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr><th>#</th><th>Barang</th><th>SN</th><th>Qty</th><th>Tgl Pinjam</th><th>Tgl Kembali</th><th>Keterangan</th><th>Status</th><th>Kondisi</th></tr>
        </thead>
        <tbody>
        <?php $i = 1; while ($r = $barangList->fetch_assoc()): ?>
        <tr>
          <td><?= $i++ ?></td>
          <td class="fw-semibold"><?= sanitize($r['namaBarang']) ?></td>
          <td><small><code><?= sanitize($r['SN']) ?></code></small></td>
          <td><?= $r['qty'] ?></td>
          <td><?= $r['tgl_pinjam'] ?></td>
          <td><?= $r['tgl_kembali'] ?></td>
          <td><?= sanitize(mb_strimwidth($r['keterangan'], 0, 40, '...')) ?></td>
          <td><?= badgeApproval($r['status_approval']) ?></td>
          <td>
            <?php if ($r['status_kondisi']): ?>
              <span class="badge bg-secondary"><?= sanitize($r['status_kondisi']) ?></span>
            <?php else: ?><span class="text-muted">-</span><?php endif; ?>
          </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php else: ?>
<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr><th>#</th><th>Ruangan</th><th>Kode</th><th>Waktu Mulai</th><th>Waktu Selesai</th><th>Keterangan</th><th>Status</th><th>Kondisi</th></tr>
        </thead>
        <tbody>
        <?php $i = 1; while ($r = $ruanganList->fetch_assoc()): ?>
        <tr>
          <td><?= $i++ ?></td>
          <td class="fw-semibold"><?= sanitize($r['namaRuangan']) ?></td>
          <td><code><?= $r['kodRuangan'] ?></code></td>
          <td><?= date('d/m/Y H:i', strtotime($r['wkt_pinjam'])) ?></td>
          <td><?= date('d/m/Y H:i', strtotime($r['wkt_kembali'])) ?></td>
          <td><?= sanitize(mb_strimwidth($r['keterangan'], 0, 40, '...')) ?></td>
          <td><?= badgeApproval($r['status_approval']) ?></td>
          <td>
            <?php if ($r['status_kondisi']): ?>
              <span class="badge bg-secondary"><?= sanitize($r['status_kondisi']) ?></span>
            <?php else: ?><span class="text-muted">-</span><?php endif; ?>
          </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="mt-3 d-flex gap-2">
  <a href="<?= BASE_URL ?>/pages/peminjam/pinjam_barang.php" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg me-1"></i>Pinjam Barang Baru
  </a>
  <a href="<?= BASE_URL ?>/pages/peminjam/pinjam_ruangan.php" class="btn btn-success btn-sm">
    <i class="bi bi-plus-lg me-1"></i>Pinjam Ruangan Baru
  </a>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
