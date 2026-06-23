<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('peminjam');

$uid = getUserId();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_ruangan  = (int)$_POST['id_ruangan'];
    $phone       = $conn->real_escape_string(trim($_POST['phone']));
    $wkt_pinjam  = $conn->real_escape_string($_POST['wkt_pinjam']);
    $wkt_kembali = $conn->real_escape_string($_POST['wkt_kembali']);
    $keterangan  = $conn->real_escape_string(trim($_POST['keterangan']));

    $buktiFoto = null;
    if (!empty($_FILES['buktiFoto']['name'])) {
        $buktiFoto = uploadFile($_FILES['buktiFoto'], 'ruangan');
        if (!$buktiFoto) {
            alert('Format/ukuran file tidak valid (maks 5MB).', 'danger');
            redirect('pages/peminjam/pinjam_ruangan.php');
        }
    }

    $user  = $conn->query("SELECT * FROM users WHERE id=$uid")->fetch_assoc();
    $nama  = $conn->real_escape_string($user['nama']);
    $jenis = $conn->real_escape_string($user['jenis_identitas']);
    $nomor = $conn->real_escape_string($user['nomor_identitas']);
    $foto_val = $buktiFoto ? "'" . $conn->real_escape_string($buktiFoto) . "'" : "NULL";

    $conn->query("INSERT INTO form_peminjamanRuangan
        (id_user, id_ruangan, nama, jenis_identitas, nomor_identitas, phone,
         wkt_pinjam, wkt_kembali, keterangan, buktiFoto)
        VALUES ($uid, $id_ruangan, '$nama', '$jenis', '$nomor', '$phone',
                '$wkt_pinjam', '$wkt_kembali', '$keterangan', $foto_val)");

    alert('Permohonan peminjaman ruangan berhasil dikirim. Menunggu persetujuan admin.');
    redirect('pages/peminjam/riwayat.php');
}

$ruanganList = $conn->query("SELECT * FROM ruangan WHERE status_ruangan='Tersedia' ORDER BY namaRuangan");
$pageTitle = 'Pinjam Ruangan';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="row justify-content-center">
<div class="col-lg-8">
<div class="card">
  <div class="card-header"><i class="bi bi-door-open me-2 text-success"></i>Form Peminjaman Ruangan</div>
  <div class="card-body">
    <form method="POST" enctype="multipart/form-data">

      <div class="mb-3">
        <label class="form-label fw-semibold">Pilih Ruangan <span class="text-danger">*</span></label>
        <select name="id_ruangan" class="form-select" required>
          <option value="">-- Pilih Ruangan --</option>
          <?php while ($r = $ruanganList->fetch_assoc()): ?>
          <option value="<?= $r['id'] ?>">
            <?= sanitize($r['namaRuangan']) ?> (Kode: <?= $r['SN'] ?>)
          </option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Nomor HP <span class="text-danger">*</span></label>
        <input type="text" name="phone" class="form-control" placeholder="08xxxxxxxxxx" required>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Waktu Mulai Pinjam <span class="text-danger">*</span></label>
          <input type="datetime-local" name="wkt_pinjam" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Waktu Selesai <span class="text-danger">*</span></label>
          <input type="datetime-local" name="wkt_kembali" class="form-control" required>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Keterangan / Keperluan <span class="text-danger">*</span></label>
        <textarea name="keterangan" class="form-control" rows="3"
          placeholder="Jelaskan keperluan peminjaman ruangan..." required></textarea>
      </div>

      <div class="mb-4">
        <label class="form-label fw-semibold">Bukti Foto <small class="text-muted">(opsional, maks 5MB)</small></label>
        <input type="file" name="buktiFoto" class="form-control" accept="image/*" data-preview="previewFoto">
        <img id="previewFoto" src="" class="mt-2 thumb d-none" alt="Preview">
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-success">
          <i class="bi bi-send me-2"></i>Kirim Permohonan
        </button>
        <a href="<?= BASE_URL ?>/dashboard.php" class="btn btn-secondary">Batal</a>
      </div>
    </form>
  </div>
</div>
</div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
