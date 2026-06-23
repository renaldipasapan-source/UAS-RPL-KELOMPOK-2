<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$role = getRole();
$uid  = getUserId();

// ─── Stats ───────────────────────────────────────────────────
$statsBarang   = $conn->query("SELECT COUNT(*) c FROM Barang")->fetch_assoc()['c'];
$statsRuangan  = $conn->query("SELECT COUNT(*) c FROM ruangan")->fetch_assoc()['c'];
$statsPinjamB  = $conn->query("SELECT COUNT(*) c FROM form_peminjamanBarang WHERE status_approval='Waiting'")->fetch_assoc()['c'];
$statsPinjamR  = $conn->query("SELECT COUNT(*) c FROM form_peminjamanRuangan WHERE status_approval='Waiting'")->fetch_assoc()['c'];
$statsPengaduan= $conn->query("SELECT COUNT(*) c FROM form_pengaduanMasalah WHERE status_pengaduan='Waiting'")->fetch_assoc()['c'];

if ($role === 'peminjam') {
    $myBarang  = $conn->query("SELECT COUNT(*) c FROM form_peminjamanBarang WHERE id_user=$uid")->fetch_assoc()['c'];
    $myRuangan = $conn->query("SELECT COUNT(*) c FROM form_peminjamanRuangan WHERE id_user=$uid")->fetch_assoc()['c'];
    $stockBarang = $conn->query("SELECT COUNT(*) c FROM Barang WHERE status_barang='Tersedia'")->fetch_assoc()['c'];
    $stockRuangan = $conn->query("SELECT COUNT(*) c FROM ruangan WHERE status_ruangan='Tersedia'")->fetch_assoc()['c'];
}

// Recent activity
$recentB = $conn->query("
    SELECT fpb.*, b.namaBarang FROM form_peminjamanBarang fpb
    JOIN Barang b ON b.id=fpb.id_barang
    ORDER BY fpb.created_at DESC LIMIT 5");
$recentR = $conn->query("
    SELECT fpr.*, r.namaRuangan FROM form_peminjamanRuangan fpr
    JOIN ruangan r ON r.id=fpr.id_ruangan
    ORDER BY fpr.created_at DESC LIMIT 5");

$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<h4 class="mb-4 fw-bold"><i class="bi bi-speedometer2 me-2 text-primary"></i>Dashboard
  <small class="fs-6 fw-normal text-muted ms-2">Selamat datang, <?= sanitize($_SESSION['nama']) ?>!</small>
</h4>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
<?php if ($role !== 'peminjam'): ?>
  <div class="col-6 col-md-3">
    <div class="stat-card bg-primary">
      <div class="stat-icon"><i class="bi bi-box-seam"></i></div>
      <div>
        <div class="stat-num"><?= $statsBarang ?></div>
        <div class="stat-label">Total Barang</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card bg-success">
      <div class="stat-icon"><i class="bi bi-building"></i></div>
      <div>
        <div class="stat-num"><?= $statsRuangan ?></div>
        <div class="stat-label">Total Ruangan</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card bg-warning text-dark">
      <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
      <div>
        <div class="stat-num"><?= $statsPinjamB + $statsPinjamR ?></div>
        <div class="stat-label">Menunggu Approval</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card bg-danger">
      <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
      <div>
        <div class="stat-num"><?= $statsPengaduan ?></div>
        <div class="stat-label">Pengaduan Pending</div>
      </div>
    </div>
  </div>
<?php else: ?>
  <div class="col-12 col-md-6">
    <div class="stat-card bg-primary">
      <div class="stat-icon"><i class="bi bi-box"></i></div>
      <div>
        <div class="stat-num"><?= $myBarang ?></div>
        <div class="stat-label">Peminjaman Barang Saya</div>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6">
    <div class="stat-card bg-success">
      <div class="stat-icon"><i class="bi bi-door-open"></i></div>
      <div>
        <div class="stat-num"><?= $myRuangan ?></div>
        <div class="stat-label">Peminjaman Ruangan Saya</div>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6">
    <div class="stat-card bg-info text-white">
      <div class="stat-icon"><i class="bi bi-box-seam"></i></div>
      <div>
        <div class="stat-num"><?= $stockBarang ?></div>
        <div class="stat-label">Stock Barang Tersedia</div>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6">
    <div class="stat-card bg-secondary text-white">
      <div class="stat-icon"><i class="bi bi-building"></i></div>
      <div>
        <div class="stat-num"><?= $stockRuangan ?></div>
        <div class="stat-label">Stock Ruangan Tersedia</div>
      </div>
    </div>
  </div>
<?php endif; ?>
</div>

<div class="row g-3">
  <!-- Peminjaman Barang Terbaru -->
  <div class="col-12 col-lg-6">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-box me-2 text-primary"></i>Peminjaman Barang Terbaru</span>
        <?php if ($role === 'admin'): ?>
        <a href="<?= BASE_URL ?>/pages/admin/peminjaman_barang.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
        <?php elseif ($role === 'kaprodi'): ?>
        <a href="<?= BASE_URL ?>/pages/kaprodi/peminjaman_barang.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
        <?php else: ?>
        <a href="<?= BASE_URL ?>/pages/peminjam/riwayat.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
        <?php endif; ?>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead><tr><th>Peminjam</th><th>Barang</th><th>Tgl Pinjam</th><th>Status</th></tr></thead>
            <tbody>
            <?php while ($r = $recentB->fetch_assoc()): ?>
            <tr>
              <td><?= sanitize($r['nama']) ?></td>
              <td><?= sanitize($r['namaBarang']) ?></td>
              <td><?= $r['tgl_pinjam'] ?></td>
              <td><?= badgeApproval($r['status_approval']) ?></td>
            </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Peminjaman Ruangan Terbaru -->
  <div class="col-12 col-lg-6">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-door-open me-2 text-success"></i>Peminjaman Ruangan Terbaru</span>
        <?php if ($role === 'admin'): ?>
        <a href="<?= BASE_URL ?>/pages/admin/peminjaman_ruangan.php" class="btn btn-sm btn-outline-success">Lihat Semua</a>
        <?php elseif ($role === 'kaprodi'): ?>
        <a href="<?= BASE_URL ?>/pages/kaprodi/peminjaman_ruangan.php" class="btn btn-sm btn-outline-success">Lihat Semua</a>
        <?php else: ?>
        <a href="<?= BASE_URL ?>/pages/peminjam/riwayat.php" class="btn btn-sm btn-outline-success">Lihat Semua</a>
        <?php endif; ?>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead><tr><th>Peminjam</th><th>Ruangan</th><th>Waktu</th><th>Status</th></tr></thead>
            <tbody>
            <?php while ($r = $recentR->fetch_assoc()): ?>
            <tr>
              <td><?= sanitize($r['nama']) ?></td>
              <td><?= sanitize($r['namaRuangan']) ?></td>
              <td><?= date('d/m H:i', strtotime($r['wkt_pinjam'])) ?></td>
              <td><?= badgeApproval($r['status_approval']) ?></td>
            </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
