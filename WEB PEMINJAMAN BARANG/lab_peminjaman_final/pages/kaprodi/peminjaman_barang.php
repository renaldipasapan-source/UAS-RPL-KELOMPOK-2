<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('kaprodi');

// ─── Approval (hanya kaprodi yang bisa) ──────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id     = (int)$_POST['id'];
    $action = $conn->real_escape_string($_POST['action']);
    $conn->query("UPDATE form_peminjamanBarang SET status_approval='$action' WHERE id=$id");
    if ($action === 'Approved') {
        $rec = $conn->query("SELECT id_barang, qty FROM form_peminjamanBarang WHERE id=$id")->fetch_assoc();
        $conn->query("UPDATE Barang SET
            qty = GREATEST(0, qty - {$rec['qty']}),
            status_barang = IF(qty - {$rec['qty']} <= 0, 'Dipakai', 'Tersedia')
            WHERE id = {$rec['id_barang']}");
    }
    alert($action === 'Approved' ? 'Peminjaman disetujui.' : 'Peminjaman ditolak.',
          $action === 'Approved' ? 'success' : 'warning');
    redirect('pages/kaprodi/peminjaman_barang.php');
}

$filter = $_GET['status'] ?? '';
$q      = trim($_GET['q'] ?? '');
$where_parts = [];
if ($filter) {
  $where_parts[] = "WHERE fpb.status_approval='$filter'";
}
if ($q) {
  $esc = $conn->real_escape_string($q);
  $where_parts[] = "(
    fpb.nama LIKE '%$esc%' OR fpb.nomor_identitas LIKE '%$esc%' OR fpb.jenis_identitas LIKE '%$esc%' OR
    fpb.phone LIKE '%$esc%' OR b.namaBarang LIKE '%$esc%' OR b.SN LIKE '%$esc%' OR
    fpb.keterangan LIKE '%$esc%' OR fpb.status_approval LIKE '%$esc%'
  )";
}
if ($where_parts) {
  $first = array_shift($where_parts);
  $where = $first . ($where_parts ? ' AND ' . implode(' AND ', $where_parts) : '');
} else {
  $where = '';
}
$list   = $conn->query("
    SELECT fpb.*, b.namaBarang, t.nama AS tipe FROM form_peminjamanBarang fpb
    JOIN Barang b ON b.id=fpb.id_barang
    LEFT JOIN TypeBarang t ON t.id=b.id_type
    $where ORDER BY fpb.created_at DESC");

// Summary stats
$total    = $conn->query("SELECT COUNT(*) c FROM form_peminjamanBarang")->fetch_assoc()['c'];
$waiting  = $conn->query("SELECT COUNT(*) c FROM form_peminjamanBarang WHERE status_approval='Waiting'")->fetch_assoc()['c'];
$approved = $conn->query("SELECT COUNT(*) c FROM form_peminjamanBarang WHERE status_approval='Approved'")->fetch_assoc()['c'];
$deny     = $conn->query("SELECT COUNT(*) c FROM form_peminjamanBarang WHERE status_approval='Deny'")->fetch_assoc()['c'];

$pageTitle = 'Kelola Peminjaman Barang';
require_once __DIR__ . '/../../includes/header.php';
?>

<h4 class="fw-bold mb-4"><i class="bi bi-box me-2 text-primary"></i>Peminjaman Barang</h4>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <form method="GET" class="flex-grow-1">
    <div class="input-group input-group-sm" style="max-width:420px;">
      <input type="search" name="q" class="form-control" placeholder="Cari peminjaman..."
        value="<?= sanitize($q) ?>">
      <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
    </div>
  </form>
  <div class="d-flex gap-2 flex-wrap">
    <?php foreach ([''=>'Semua','Waiting'=>'Waiting','Approved'=>'Approved','Deny'=>'Deny'] as $k=>$v): ?>
    <a href="?status=<?= $k ?>&q=<?= urlencode($q) ?>"
       class="btn btn-sm <?= $filter === $k ? 'btn-primary' : 'btn-outline-secondary' ?>">
      <?= $v ?>
    </a>
    <?php endforeach; ?>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="card text-center p-3">
      <div class="fw-bold fs-4 text-primary"><?= $total ?></div>
      <small class="text-muted">Total</small>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card text-center p-3">
      <div class="fw-bold fs-4 text-warning"><?= $waiting ?></div>
      <small class="text-muted">Waiting</small>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card text-center p-3">
      <div class="fw-bold fs-4 text-success"><?= $approved ?></div>
      <small class="text-muted">Approved</small>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card text-center p-3">
      <div class="fw-bold fs-4 text-danger"><?= $deny ?></div>
      <small class="text-muted">Deny</small>
    </div>
  </div>
</div>

<div class="mb-3 d-flex gap-2 flex-wrap">
  <?php foreach ([''=>'Semua','Waiting'=>'Waiting','Approved'=>'Approved','Deny'=>'Deny'] as $k=>$v): ?>
  <a href="?status=<?= $k ?>" class="btn btn-sm <?= $filter===$k ? 'btn-primary' : 'btn-outline-secondary' ?>"><?= $v ?></a>
  <?php endforeach; ?>
</div>

<div class="card">
<div class="card-body p-0">
<div class="table-responsive">
<table class="table table-hover mb-0">
  <thead>
    <tr>
      <th>#</th><th>Peminjam</th><th>Identitas</th><th>Barang</th><th>Tipe</th>
      <th>Qty</th><th>Tgl Pinjam</th><th>Tgl Kembali</th><th>Keterangan</th>
      <th>Bukti</th><th>Status</th><th>Kondisi</th><th>Aksi</th>
    </tr>
  </thead>
  <tbody>
  <?php $i=1; while ($r = $list->fetch_assoc()): ?>
  <tr>
    <td><?= $i++ ?></td>
    <td><?= sanitize($r['nama']) ?></td>
    <td><small><?= sanitize($r['jenis_identitas']) ?>: <?= sanitize($r['nomor_identitas']) ?></small></td>
    <td><?= sanitize($r['namaBarang']) ?></td>
    <td><small><?= sanitize($r['tipe'] ?? '-') ?></small></td>
    <td><?= $r['qty'] ?></td>
    <td><?= $r['tgl_pinjam'] ?></td>
    <td><?= $r['tgl_kembali'] ?></td>
    <td><small><?= sanitize(mb_strimwidth($r['keterangan'],0,40,'...')) ?></small></td>
    <td>
      <?php if ($r['buktiFoto']): ?>
        <a href="<?= UPLOAD_URL . $r['buktiFoto'] ?>" target="_blank">
          <img src="<?= UPLOAD_URL . $r['buktiFoto'] ?>" class="thumb" alt="foto">
        </a>
      <?php else: ?><span class="text-muted">-</span><?php endif; ?>
    </td>
    <td><?= badgeApproval($r['status_approval']) ?></td>
    <td><?= $r['status_kondisi'] ? '<span class="badge bg-secondary">'.sanitize($r['status_kondisi']).'</span>' : '-' ?></td>
    <td>
      <?php if ($r['status_approval'] === 'Waiting'): ?>
        <form method="POST" class="d-inline">
          <input type="hidden" name="id" value="<?= $r['id'] ?>">
          <button name="action" value="Approved" class="btn btn-sm btn-success py-0 px-2"
            onclick="return confirm('Setujui peminjaman ini?')">
            <i class="bi bi-check-lg"></i> Setuju
          </button>
          <button name="action" value="Deny" class="btn btn-sm btn-danger py-0 px-2 mt-1"
            onclick="return confirm('Tolak peminjaman ini?')">
            <i class="bi bi-x-lg"></i> Tolak
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
