<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

// Already logged in → go to dashboard
if (isLoggedIn()) redirect('dashboard.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomor    = trim($_POST['nomor_identitas'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($nomor === '') {
        $error = 'Nomor identitas wajib diisi.';
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE nomor_identitas = ? LIMIT 1");
        $stmt->bind_param('s', $nomor);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!$user) {
            $error = 'Nomor identitas tidak ditemukan.';
        } elseif ($user['role'] === 'peminjam') {
            // Peminjam tidak perlu password
            $_SESSION['user_id']         = $user['id'];
            $_SESSION['nama']            = $user['nama'];
            $_SESSION['role']            = $user['role'];
            $_SESSION['nomor_identitas'] = $user['nomor_identitas'];
            $_SESSION['jenis_identitas'] = $user['jenis_identitas'];
            redirect('dashboard.php');
        } else {
            // Admin / Kaprodi — butuh password
            if ($password === '') {
                $error = 'Password wajib diisi.';
            } elseif (!password_verify($password, $user['password'])) {
                $error = 'Password salah.';
            } else {
                $_SESSION['user_id']         = $user['id'];
                $_SESSION['nama']            = $user['nama'];
                $_SESSION['role']            = $user['role'];
                $_SESSION['nomor_identitas'] = $user['nomor_identitas'];
                $_SESSION['jenis_identitas'] = $user['jenis_identitas'];
                redirect('dashboard.php');
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — Lab Peminjaman</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="login-bg">
<div class="login-wrapper">
  <div class="login-card">
    <div class="text-center mb-4">
      <div class="logo mb-2"><i class="bi bi-hdd-network-fill"></i></div>
      <h4 class="fw-bold mb-0">Lab Jaringan</h4>
      <p class="text-muted small">Sistem Peminjaman Barang & Ruangan</p>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
      <i class="bi bi-exclamation-circle me-1"></i><?= sanitize($error) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label fw-semibold">Nomor Identitas <span class="text-danger">*</span></label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
          <input type="text" name="nomor_identitas" class="form-control"
            placeholder="NIM / NIP / NIK"
            value="<?= sanitize($_POST['nomor_identitas'] ?? '') ?>" required>
        </div>
        <div class="form-text">Peminjam (mahasiswa/dosen) tidak perlu mengisi password.</div>
      </div>

      <div class="mb-4" id="passwordSection">
        <label class="form-label fw-semibold">Password <small class="text-muted">(Admin/Kaprodi)</small></label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-lock"></i></span>
          <input type="password" name="password" id="passwordInput" class="form-control" placeholder="Masukkan password">
          <button class="btn btn-outline-secondary" type="button" id="togglePwd">
            <i class="bi bi-eye" id="eyeIcon"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
        <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
      </button>
    </form>

    <hr class="my-4">
    <div class="small text-muted text-center">
      <strong>Demo Login:</strong><br>
      Kaprodi: <code>198501012010011001</code> / <code>password</code><br>
      Admin: <code>199203152015012002</code> / <code>password</code><br>
      Peminjam: <code>20210001</code> (tanpa password)
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('togglePwd').addEventListener('click', function () {
  const inp = document.getElementById('passwordInput');
  const eye = document.getElementById('eyeIcon');
  if (inp.type === 'password') { inp.type = 'text'; eye.className = 'bi bi-eye-slash'; }
  else { inp.type = 'password'; eye.className = 'bi bi-eye'; }
});
</script>
</body>
</html>
