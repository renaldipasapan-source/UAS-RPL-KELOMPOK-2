<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('peminjam');

$barang = $conn->query("SELECT b.*, t.nama AS tipe FROM Barang b LEFT JOIN TypeBarang t ON t.id=b.id_type ORDER BY b.namaBarang");
$ruangan = $conn->query("SELECT * FROM ruangan ORDER BY namaRuangan");

$pageTitle = 'Data Stock';
require_once __DIR__ . '/../../includes/header.php';
?>

<h4 class="fw-bold mb-4"><i class="bi bi-stack me-2 text-info"></i>Data Stock</h4>

<div class="row g-4 mb-4">
  <div class="col-12 col-lg-6">
    <div class="card">
      <div class="card-header bg-info bg-opacity-10 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <form method="GET" class="flex-grow-1 me-2">
          <div class="input-group input-group-sm" style="max-width:420px;">
            <input type="search" name="q" class="form-control" placeholder="Cari stock barang...">
            <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
          </div>
        </form>
        <a href="<?= BASE_URL ?>/pages/peminjam/pinjam_barang.php" class="btn btn-sm btn-info">Pinjam Barang</a>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr><th>#</th><th>Nama Barang</th><th>SN</th><th>Tipe</th><th>Qty</th><th>Status</th></tr>
            </thead>
            <tbody>
            <?php $i = 1; while ($r = $barang->fetch_assoc()): ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= sanitize($r['namaBarang']) ?></td>
              <td><code><?= sanitize($r['SN']) ?></code></td>
              <td><?= sanitize($r['tipe'] ?? '-') ?></td>
              <td><?= $r['qty'] ?></td>
              <td><?= badgeStatus($r['status_barang']) ?></td>
            </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-6">
    <div class="card">
      <div class="card-header bg-success bg-opacity-10 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <form method="GET" class="flex-grow-1 me-2">
          <div class="input-group input-group-sm" style="max-width:420px;">
            <input type="search" name="q2" class="form-control" placeholder="Cari ruangan...">
            <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
          </div>
        </form>
        <a href="<?= BASE_URL ?>/pages/peminjam/pinjam_ruangan.php" class="btn btn-sm btn-success">Pinjam Ruangan</a>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr><th>#</th><th>Nama Ruangan</th><th>Kode</th><th>Status</th></tr>
            </thead>
            <tbody>
            <?php $i = 1; while ($r = $ruangan->fetch_assoc()): ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= sanitize($r['namaRuangan']) ?></td>
              <td><code><?= sanitize($r['SN']) ?></code></td>
              <td><?= badgeStatus($r['status_ruangan']) ?></td>
            </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php';
