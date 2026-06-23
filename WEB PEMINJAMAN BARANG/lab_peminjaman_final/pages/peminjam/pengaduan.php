<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('peminjam');

alert('Pengaduan hanya dapat dilakukan oleh admin melalui riwayat peminjaman.', 'warning');
redirect('pages/peminjam/riwayat.php');

$uid = getUserId();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipe      = $conn->real_escape_string($_POST['tipe_peminjaman']);
    $id_form_b = !empty($_POST['id_form_b']) ? (int)$_POST['id_form_b'] : 'NULL';
    $id_form_r = !empty($_POST['id_form_r']) ? (int)$_POST['id_form_r'] : 'NULL';
    $deskripsi = $conn->real_escape_string(trim($_POST['deskripsi_masalah']));
    $prioritas = (int)$_POST['tingkat_prioritas'];
    $tgl       = $conn->real_escape_string($_POST['tgl_pengaduan']);

    if (empty($_FILES['foto_pengaduan']['name'])) {
        alert('Foto pengaduan wajib diupload.', 'danger');
        redirect('pages/peminjam/pengaduan.php');
    }

    $foto = uploadFile($_FILES['foto_pengaduan'], 'pengaduan');
    if (!$foto) {
        alert('Format/ukuran file tidak valid (maks 5MB, jpg/png).', 'danger');
        redirect('pages/peminjam/pengaduan.php');
    }
    $foto_esc = $conn->real_escape_string($foto);

    $conn->query("INSERT INTO form_pengaduanMasalah
        (id_user, tipe_peminjaman, id_form_peminjamanBarang, id_form_peminjamanRuangan,
         deskripsi_masalah, tingkat_prioritas, tgl_pengaduan, foto_pengaduan)
        VALUES ($uid, '$tipe', $id_form_b, $id_form_r, '$deskripsi', $prioritas, '$tgl', '$foto_esc')");

    alert('Pengaduan berhasil dikirim. Admin akan segera menangani.');
    redirect('pages/peminjam/pengaduan.php');
}

$myBarang  = $conn->query("
    SELECT fpb.id, b.namaBarang FROM form_peminjamanBarang fpb
    JOIN Barang b ON b.id = fpb.id_barang
    WHERE fpb.id_user = $uid AND fpb.status_approval = 'Approved'
    ORDER BY fpb.tgl_pinjam DESC");

$myRuangan = $conn->query("
    SELECT fpr.id, r.namaRuangan FROM form_peminjamanRuangan fpr
    JOIN ruangan r ON r.id = fpr.id_ruangan
    WHERE fpr.id_user = $uid AND fpr.status_approval = 'Approved'
    ORDER BY fpr.wkt_pinjam DESC");

$myPengaduan = $conn->query("
    SELECT fpm.*,
      COALESCE(b.namaBarang, r.namaRuangan) AS objek
    FROM form_pengaduanMasalah fpm
    LEFT JOIN form_peminjamanBarang fpb ON fpb.id = fpm.id_form_peminjamanBarang
    LEFT JOIN Barang b ON b.id = fpb.id_barang
    LEFT JOIN form_peminjamanRuangan fpr ON fpr.id = fpm.id_form_peminjamanRuangan
    LEFT JOIN ruangan r ON r.id = fpr.id_ruangan
    WHERE fpm.id_user = $uid
    ORDER BY fpm.created_at DESC");

$pageTitle = 'Pengaduan Masalah';
require_once __DIR__ . '/../../includes/header.php';
?>

<h4 class="mb-4 fw-bold"><i class="bi bi-exclamation-triangle me-2 text-danger"></i>Pengaduan Masalah</h4>

<div class="row g-4">
  <!-- Form Kirim -->
  <div class="col-lg-5">
    <div class="card">
      <div class="card-header"><i class="bi bi-plus-circle me-2"></i>Kirim Pengaduan Baru</div>
      <div class="card-body">
        <form method="POST" enctype="multipart/form-data">

          <div class="mb-3">
            <label class="form-label fw-semibold">Tipe Peminjaman <span class="text-danger">*</span></label>
            <select name="tipe_peminjaman" id="tipePeminjaman" class="form-select" required>
              <option value="">-- Pilih --</option>
              <option value="Barang">Barang</option>
              <option value="Ruangan">Ruangan</option>
            </select>
          </div>

          <div class="mb-3 d-none" id="refBarang">
            <label class="form-label fw-semibold">Terkait Peminjaman Barang</label>
            <select name="id_form_b" class="form-select">
              <option value="">-- Pilih (opsional) --</option>
              <?php while ($b = $myBarang->fetch_assoc()): ?>
              <option value="<?= $b['id'] ?>">#<?= $b['id'] ?> — <?= sanitize($b['namaBarang']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="mb-3 d-none" id="refRuangan">
            <label class="form-label fw-semibold">Terkait Peminjaman Ruangan</label>
            <select name="id_form_r" class="form-select">
              <option value="">-- Pilih (opsional) --</option>
              <?php while ($r = $myRuangan->fetch_assoc()): ?>
              <option value="<?= $r['id'] ?>">#<?= $r['id'] ?> — <?= sanitize($r['namaRuangan']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Deskripsi Masalah <span class="text-danger">*</span></label>
            <textarea name="deskripsi_masalah" class="form-control" rows="4"
              placeholder="Jelaskan masalah secara detail..." required></textarea>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Tingkat Prioritas</label>
              <select name="tingkat_prioritas" class="form-select">
                <option value="1">1 — Rendah</option>
                <option value="2">2 — Sedang</option>
                <option value="3">3 — Tinggi</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Tanggal Pengaduan <span class="text-danger">*</span></label>
              <input type="date" name="tgl_pengaduan" class="form-control"
                value="<?= date('Y-m-d') ?>" required>
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label fw-semibold">Foto Pengaduan <span class="text-danger">*</span></label>
            <input type="file" name="foto_pengaduan" class="form-control"
              accept="image/*" data-preview="prvFoto" required>
            <img id="prvFoto" src="" class="mt-2 thumb d-none" alt="Preview">
          </div>

          <button type="submit" class="btn btn-danger w-100">
            <i class="bi bi-send me-2"></i>Kirim Pengaduan
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- Riwayat -->
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header"><i class="bi bi-list-check me-2"></i>Riwayat Pengaduan Saya</div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr><th>#</th><th>Tipe</th><th>Deskripsi</th><th>Prioritas</th><th>Status</th><th>Tgl</th><th>Resolusi</th></tr>
            </thead>
            <tbody>
            <?php $i = 1; while ($r = $myPengaduan->fetch_assoc()): ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= sanitize($r['tipe_peminjaman']) ?></td>
              <td>
                <?php if ($r['objek']): ?>
                  <small class="text-muted d-block"><?= sanitize($r['objek']) ?></small>
                <?php endif; ?>
                <?= sanitize(mb_strimwidth($r['deskripsi_masalah'], 0, 40, '...')) ?>
              </td>
              <td><?= priorityLabel($r['tingkat_prioritas']) ?></td>
              <td><?= badgeApproval($r['status_pengaduan']) ?></td>
              <td><?= $r['tgl_pengaduan'] ?></td>
              <td>
                <?php if ($r['deskripsi_resolusi']): ?>
                  <small><?= sanitize(mb_strimwidth($r['deskripsi_resolusi'], 0, 40, '...')) ?></small>
                <?php else: ?><span class="text-muted">-</span><?php endif; ?>
              </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('tipePeminjaman').addEventListener('change', function () {
  document.getElementById('refBarang').classList.add('d-none');
  document.getElementById('refRuangan').classList.add('d-none');
  if (this.value === 'Barang') document.getElementById('refBarang').classList.remove('d-none');
  if (this.value === 'Ruangan') document.getElementById('refRuangan').classList.remove('d-none');
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
