<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('kaprodi');

// ─── Approval (hanya kaprodi yang bisa) ──────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id     = (int)$_POST['id'];
    $action = $conn->real_escape_string($_POST['action']);
    $conn->query("UPDATE form_peminjamanRuangan SET status_approval='$action' WHERE id=$id");
    if ($action === 'Approved') {
        $rec = $conn->query("SELECT id_ruangan FROM form_peminjamanRuangan WHERE id=$id")->fetch_assoc();
        $conn->query("UPDATE ruangan SET status_ruangan='Dipakai' WHERE id={$rec['id_ruangan']}");
    }
    alert($action === 'Approved' ? 'Peminjaman disetujui.' : 'Peminjaman ditolak.',
          $action === 'Approved' ? 'success' : 'warning');
    redirect('pages/kaprodi/peminjaman_ruangan.php');
}

$filter = $_GET['status'] ?? '';
$where  = $filter ? "WHERE fpr.status_approval='$filter'" : '';
$list   = $conn->query("
    SELECT fpr.*, r.namaRuangan FROM form_peminjamanRuangan fpr
    JOIN ruangan r ON r.id=fpr.id_ruangan
    $where ORDER BY fpr.created_at DESC");

$total    = $conn->query("SELECT COUNT(*) c FROM form_peminjamanRuangan")->fetch_assoc()['c'];
$waiting  = $conn->query("SELECT COUNT(*) c FROM form_peminjamanRuangan WHERE status_approval='Waiting'")->fetch_assoc()['c'];
$approved = $conn->query("SELECT COUNT(*) c FROM form_peminjamanRuangan WHERE status_approval='Approved'")->fetch_assoc()['c'];
$deny     = $conn->query("SELECT COUNT(*) c FROM form_peminjamanRuangan WHERE status_approval='Deny'")->fetch_assoc()['c'];

$pageTitle = 'Kelola Peminjaman Ruangan';
require_once __DIR__ . '/../../includes/header.php';
?>

<h4 class="fw-bold mb-4"><i class="bi bi-door-open me-2 text-success"></i>Peminjaman Ruangan</h4>

<div class="row g-3 mb-4">
  <div class="col-6 col-md-3"><div class="card text-center p-3"><div class="fw-bold fs-4 text-primary"><?= $total ?></div><small class="text-muted">Total</small></div></div>
  <div class="col-6 col-md-3"><div class="card text-center p-3"><div class="fw-bold fs-4 text-warning"><?= $waiting ?></div><small class="text-muted">Waiting</small></div></div>
  <div class="col-6 col-md-3"><div class="card text-center p-3"><div class="fw-bold fs-4 text-success"><?= $approved ?></div><small class="text-muted">Approved</small></div></div>
  <div class="col-6 col-md-3"><div class="card text-center p-3"><div class="fw-bold fs-4 text-danger"><?= $deny ?></div><small class="text-muted">Deny</small></div></div>
</div>

<div class="mb-3 d-flex gap-2 flex-wrap">
  <?php foreach ([''=>'Semua','Waiting'=>'Waiting','Approved'=>'Approved','Deny'=>'Deny'] as $k=>$v): ?>
  <a href="?status=<?= $k ?>" class="btn btn-sm <?= $filter===$k ? 'btn-success' : 'btn-outline-secondary' ?>"><?= $v ?></a>
  <?php endforeach; ?>
</div>

<div class="card">
<div class="card-body p-0">
<div class="table-responsive">
<table class="table table-hover mb-0">
  <thead>
    <tr>
      <th>#</th><th>Peminjam</th><th>Identitas</th><th>Ruangan</th>
      <th>Waktu Mulai</th><th>Waktu Selesai</th><th>Keterangan</th>
      <th>Status</th><th>Kondisi</th><th>Aksi</th>
    </tr>
  </thead>
  <tbody>
  <?php $i=1; while ($r = $list->fetch_assoc()): ?>
  <tr>
    <td><?= $i++ ?></td>
    <td><?= sanitize($r['nama']) ?></td>
    <td><small><?= sanitize($r['jenis_identitas']) ?>: <?= sanitize($r['nomor_identitas']) ?></small></td>
    <td><?= sanitize($r['namaRuangan']) ?></td>
    <td><?= date('d/m/Y H:i', strtotime($r['wkt_pinjam'])) ?></td>
    <td><?= date('d/m/Y H:i', strtotime($r['wkt_kembali'])) ?></td>
    <td><small><?= sanitize(mb_strimwidth($r['keterangan'],0,40,'...')) ?></small></td>
    <td><?= badgeApproval($r['status_approval']) ?></td>
    <td><?= $r['status_kondisi'] ? '<span class="badge bg-secondary">'.sanitize($r['status_kondisi']).'</span>' : '-' ?></td>
    <td>
      <?php if ($r['status_approval'] === 'Waiting'): ?>
        <form method="POST" class="d-inline">
          <input type="hidden" name="id" value="<?= $r['id'] ?>">
          <button name="action" value="Approved" class="btn btn-sm btn-success py-0 px-2"
            onclick="return confirm('Setujui peminjaman ruangan ini?')">
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
