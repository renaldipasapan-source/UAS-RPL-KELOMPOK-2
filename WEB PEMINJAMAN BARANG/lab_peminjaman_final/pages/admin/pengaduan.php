<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['laporkan_pengaduan'])) {
    $uid       = getUserId();
    $tipe      = $conn->real_escape_string($_POST['tipe_peminjaman']);
    $id_form_b = !empty($_POST['id_form_peminjamanBarang']) ? (int)$_POST['id_form_peminjamanBarang'] : 'NULL';
    $id_form_r = !empty($_POST['id_form_peminjamanRuangan']) ? (int)$_POST['id_form_peminjamanRuangan'] : 'NULL';
    $deskripsi = $conn->real_escape_string(trim($_POST['deskripsi_masalah']));
    $prioritas = (int)$_POST['tingkat_prioritas'];
    $tgl       = $conn->real_escape_string($_POST['tgl_pengaduan']);

    if (empty($_FILES['foto_pengaduan']['name'])) {
        alert('Foto pengaduan wajib diupload.', 'danger');
        redirect('pages/admin/pengaduan.php');
    }

    $foto = uploadFile($_FILES['foto_pengaduan'], 'pengaduan');
    if (!$foto) {
        alert('Format/ukuran file tidak valid (maks 5MB, jpg/png).', 'danger');
        redirect('pages/admin/pengaduan.php');
    }
    $foto_esc = $conn->real_escape_string($foto);

    $conn->query("INSERT INTO form_pengaduanMasalah
        (id_user, tipe_peminjaman, id_form_peminjamanBarang, id_form_peminjamanRuangan,
         deskripsi_masalah, tingkat_prioritas, tgl_pengaduan, foto_pengaduan)
        VALUES ($uid, '$tipe', $id_form_b, $id_form_r, '$deskripsi', $prioritas, '$tgl', '$foto_esc')");

    alert('Pengaduan berhasil dicatat. Kaprodi akan segera meninjaunya.');
    redirect('pages/admin/pengaduan.php');
}

// Admin hanya melihat riwayat data peminjaman (barang & ruangan), dan dapat membuat pengaduan dari riwayat
$filter_tipe = $_GET['tipe'] ?? '';
$filter_status = $_GET['status'] ?? '';
$q = trim($_GET['q'] ?? '');

$where_parts = [];
if ($filter_tipe) {
  $ft = $conn->real_escape_string($filter_tipe);
  $where_parts[] = "fpb.tipe_peminjaman = '$ft'";
}
if ($filter_status) {
  $fs = $conn->real_escape_string($filter_status);
  $where_parts[] = "fpb.status_approval = '$fs'";
}
$where_barang  = $where_parts ? 'WHERE ' . implode(' AND ', $where_parts) : '';

// Riwayat peminjaman barang
$list_barang = $conn->query("
    SELECT 'Barang' AS tipe_pinjam, fpb.id, fpb.nama, fpb.nomor_identitas, fpb.jenis_identitas,
           fpb.phone, b.namaBarang AS objek, fpb.qty,
           fpb.tgl_pinjam AS wkt_mulai, fpb.tgl_kembali AS wkt_selesai,
           fpb.keterangan, fpb.status_approval, fpb.tgl_serahterima,
           fpb.status_kondisi, fpb.created_at
    FROM form_peminjamanBarang fpb
    JOIN Barang b ON b.id = fpb.id_barang
    ORDER BY fpb.created_at DESC");

// Riwayat peminjaman ruangan
$list_ruangan = $conn->query("
    SELECT 'Ruangan' AS tipe_pinjam, fpr.id, fpr.nama, fpr.nomor_identitas, fpr.jenis_identitas,
           fpr.phone, r.namaRuangan AS objek, NULL AS qty,
           fpr.wkt_pinjam AS wkt_mulai, fpr.wkt_kembali AS wkt_selesai,
           fpr.keterangan, fpr.status_approval, fpr.tgl_serahterima,
           fpr.status_kondisi, fpr.created_at
    FROM form_peminjamanRuangan fpr
    JOIN ruangan r ON r.id = fpr.id_ruangan
    ORDER BY fpr.created_at DESC");

// Gabungkan data ke satu array dan urutkan berdasarkan created_at DESC
$all_data = [];
while ($r = $list_barang->fetch_assoc())  $all_data[] = $r;
while ($r = $list_ruangan->fetch_assoc()) $all_data[] = $r;
usort($all_data, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));

// Filter tipe dan status jika dipilih
if ($filter_tipe) {
    $all_data = array_filter($all_data, fn($d) => $d['tipe_pinjam'] === $filter_tipe);
}
if ($filter_status) {
    $all_data = array_filter($all_data, fn($d) => $d['status_approval'] === $filter_status);
}

// Text search on combined data
if ($q) {
  $qnorm = mb_strtolower($q);
  $all_data = array_filter($all_data, function($d) use ($qnorm) {
    $hay = mb_strtolower(implode(' ', [
      $d['nama'] ?? '', $d['nomor_identitas'] ?? '', $d['jenis_identitas'] ?? '',
      $d['objek'] ?? '', $d['deskripsi_masalah'] ?? '', $d['keterangan'] ?? '',
      $d['status_approval'] ?? ''
    ]));
    return mb_strpos($hay, $qnorm) !== false;
  });
}

$pageTitle = 'Pengaduan Masalah';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="fw-bold mb-0"><i class="bi bi-exclamation-triangle me-2 text-danger"></i>Pengaduan Masalah</h4>
</div>
<div class="alert alert-info">
  Admin dapat melaporkan masalah dari riwayat peminjaman barang atau ruangan. Pengaduan akan diteruskan ke kaprodi untuk approval.
</div>

<div class="mb-3 d-flex gap-2 flex-wrap align-items-center">
  <form method="GET" class="me-2">
    <input type="hidden" name="tipe" value="<?= sanitize($filter_tipe) ?>">
    <input type="hidden" name="status" value="<?= sanitize($filter_status) ?>">
    <div class="input-group input-group-sm" style="max-width:420px;">
      <input type="search" name="q" class="form-control" placeholder="Cari pengaduan..." value="<?= sanitize($q) ?>">
      <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
    </div>
  </form>
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
  <thead>
    <tr>
      <th>#</th><th>Tipe</th><th>Peminjam</th><th>Objek</th>
      <th>Waktu Mulai</th><th>Waktu Selesai</th><th>Keterangan</th>
      <th>Status</th><th>Kondisi</th><th>Serah Terima</th><th>Aksi</th>
    </tr>
  </thead>
  <tbody>
  <?php $i = 1; foreach ($all_data as $r): ?>
  <tr>
    <td><?= $i++ ?></td>
    <td>
      <?php if ($r['tipe_pinjam'] === 'Barang'): ?>
        <span class="badge bg-primary">Barang</span>
      <?php else: ?>
        <span class="badge bg-success">Ruangan</span>
      <?php endif; ?>
    </td>
    <td>
      <div class="fw-semibold"><?= sanitize($r['nama']) ?></div>
      <small class="text-muted"><?= sanitize($r['jenis_identitas']) ?>: <?= sanitize($r['nomor_identitas']) ?></small><br>
      <small class="text-muted"><i class="bi bi-telephone me-1"></i><?= sanitize($r['phone']) ?></small>
    </td>
    <td>
      <?= sanitize($r['objek']) ?>
      <?php if ($r['qty']): ?>
        <br><small class="text-muted">Qty: <?= $r['qty'] ?></small>
      <?php endif; ?>
    </td>
    <td><small><?= $r['wkt_mulai'] ?></small></td>
    <td><small><?= $r['wkt_selesai'] ?></small></td>
    <td><small><?= sanitize(mb_strimwidth($r['keterangan'], 0, 50, '...')) ?></small></td>
    <td><?= badgeApproval($r['status_approval']) ?></td>
    <td>
      <?= $r['status_kondisi']
          ? '<span class="badge bg-secondary">' . sanitize($r['status_kondisi']) . '</span>'
          : '<span class="text-muted">-</span>' ?>
    </td>
    <td>
      <?= $r['tgl_serahterima']
          ? '<span class="badge bg-success">' . $r['tgl_serahterima'] . '</span>'
          : '<span class="text-muted">-</span>' ?>
    </td>
    <td>
      <button class="btn btn-sm btn-danger py-0 px-2" data-bs-toggle="modal" data-bs-target="#modalLaporkan<?= $r['tipe_pinjam'] === 'Barang' ? 'B' : 'R' ?><?= $r['id'] ?>">
        <i class="bi bi-exclamation-octagon me-1"></i>Laporkan
      </button>

      <div class="modal fade" id="modalLaporkan<?= $r['tipe_pinjam'] === 'Barang' ? 'B' : 'R' ?><?= $r['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header bg-danger bg-opacity-10">
              <h5 class="modal-title"><i class="bi bi-exclamation-octagon me-2"></i>Laporkan Masalah</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
              <input type="hidden" name="laporkan_pengaduan" value="1">
              <input type="hidden" name="tipe_peminjaman" value="<?= $r['tipe_pinjam'] ?>">
              <?php if ($r['tipe_pinjam'] === 'Barang'): ?>
                <input type="hidden" name="id_form_peminjamanBarang" value="<?= $r['id'] ?>">
              <?php else: ?>
                <input type="hidden" name="id_form_peminjamanRuangan" value="<?= $r['id'] ?>">
              <?php endif; ?>
              <div class="modal-body">
                <div class="mb-3">
                  <label class="form-label fw-semibold">Peminjam</label>
                  <div class="form-control-plaintext">
                    <?= sanitize($r['nama']) ?> — <?= sanitize($r['jenis_identitas']) ?>: <?= sanitize($r['nomor_identitas']) ?>
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-semibold">Objek</label>
                  <div class="form-control-plaintext"><?= sanitize($r['objek']) ?><?= $r['qty'] ? ' (Qty: ' . sanitize($r['qty']) . ')' : '' ?></div>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-semibold">Deskripsi Masalah</label>
                  <textarea name="deskripsi_masalah" class="form-control" rows="4" required></textarea>
                </div>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Tingkat Prioritas</label>
                    <select name="tingkat_prioritas" class="form-select">
                      <option value="1">1 — Rendah</option>
                      <option value="2">2 — Sedang</option>
                      <option value="3">3 — Tinggi</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Tanggal Pengaduan</label>
                    <input type="date" name="tgl_pengaduan" class="form-control" value="<?= date('Y-m-d') ?>" required>
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-semibold">Foto Pengaduan</label>
                  <input type="file" name="foto_pengaduan" class="form-control" accept="image/*" required>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger">Kirim Pengaduan</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </td>
  </tr>
  <?php endforeach; ?>
  <?php if (empty($all_data)): ?>
  <tr><td colspan="11" class="text-center text-muted py-4">Tidak ada data peminjaman.</td></tr>
  <?php endif; ?>
  </tbody>
</table>
</div>
</div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
