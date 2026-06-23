<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('peminjam');

$uid = getUserId();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_barang   = (int)$_POST['id_barang'];
    $phone       = $conn->real_escape_string(trim($_POST['phone']));
    $tgl_pinjam  = $conn->real_escape_string($_POST['tgl_pinjam']);
    $tgl_kembali = $conn->real_escape_string($_POST['tgl_kembali']);
    $keterangan  = $conn->real_escape_string(trim($_POST['keterangan']));
    $qty         = max(1, (int)$_POST['qty']);

    $buktiFoto = null;
    if (!empty($_FILES['buktiFoto']['name'])) {
        $buktiFoto = uploadFile($_FILES['buktiFoto'], 'barang');
        if (!$buktiFoto) {
            alert('Format/ukuran file tidak valid (maks 5MB, jpg/png/gif/webp).', 'danger');
            redirect('pages/peminjam/pinjam_barang.php');
        }
    }

    // Cek stok
    $barang = $conn->query("SELECT * FROM Barang WHERE id=$id_barang")->fetch_assoc();
    if (!$barang || $barang['qty'] < $qty) {
        alert('Stok barang tidak mencukupi.', 'danger');
        redirect('pages/peminjam/pinjam_barang.php');
    }

    $user  = $conn->query("SELECT * FROM users WHERE id=$uid")->fetch_assoc();
    $nama  = $conn->real_escape_string($user['nama']);
    $jenis = $conn->real_escape_string($user['jenis_identitas']);
    $nomor = $conn->real_escape_string($user['nomor_identitas']);
    $foto_val = $buktiFoto ? "'" . $conn->real_escape_string($buktiFoto) . "'" : "NULL";

    $conn->query("INSERT INTO form_peminjamanBarang
        (id_user, id_barang, nama, jenis_identitas, nomor_identitas, phone,
         tgl_pinjam, tgl_kembali, keterangan, buktiFoto, qty)
        VALUES ($uid, $id_barang, '$nama', '$jenis', '$nomor', '$phone',
                '$tgl_pinjam', '$tgl_kembali', '$keterangan', $foto_val, $qty)");

    alert('Permohonan peminjaman barang berhasil dikirim. Menunggu persetujuan admin.');
    redirect('pages/peminjam/riwayat.php');
}

$barangList = $conn->query("
    SELECT b.*, t.nama AS tipe FROM Barang b
    LEFT JOIN TypeBarang t ON t.id = b.id_type
    WHERE b.status_barang = 'Tersedia'
    ORDER BY b.namaBarang");

$pageTitle = 'Pinjam Barang';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="row justify-content-center">
<div class="col-lg-8">
<div class="card">
  <div class="card-header"><i class="bi bi-box me-2 text-primary"></i>Form Peminjaman Barang</div>
  <div class="card-body">
    <form method="POST" enctype="multipart/form-data">

      <div class="mb-3">
        <label class="form-label fw-semibold">Pilih Barang <span class="text-danger">*</span></label>
        <select name="id_barang" class="form-select" required>
          <option value="">-- Pilih Barang --</option>
          <?php while ($b = $barangList->fetch_assoc()): ?>
          <option value="<?= $b['id'] ?>">
            <?= sanitize($b['namaBarang']) ?> | SN: <?= sanitize($b['SN']) ?>
            | Tipe: <?= sanitize($b['tipe'] ?? '-') ?> | Stok: <?= $b['qty'] ?>
          </option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Jumlah (Qty) <span class="text-danger">*</span></label>
          <input type="number" name="qty" class="form-control" min="1" value="1" required>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Nomor HP <span class="text-danger">*</span></label>
          <input type="text" name="phone" class="form-control" placeholder="08xxxxxxxxxx" required>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Tanggal Pinjam <span class="text-danger">*</span></label>
          <input type="date" name="tgl_pinjam" class="form-control"
            min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Tanggal Kembali <span class="text-danger">*</span></label>
          <input type="date" name="tgl_kembali" class="form-control"
            min="<?= date('Y-m-d') ?>" required>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Keterangan / Keperluan <span class="text-danger">*</span></label>
        <textarea name="keterangan" class="form-control" rows="3"
          placeholder="Jelaskan keperluan peminjaman..." required></textarea>
      </div>

      <div class="mb-4">
        <label class="form-label fw-semibold">Bukti Foto <small class="text-muted">(opsional, maks 5MB)</small></label>
        <input type="file" name="buktiFoto" class="form-control" accept="image/*" data-preview="previewFoto">
        <img id="previewFoto" src="" class="mt-2 thumb d-none" alt="Preview">
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
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
