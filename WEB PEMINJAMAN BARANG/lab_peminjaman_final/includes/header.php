<?php
// includes/header.php — pastikan $pageTitle di-set sebelum include ini
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? sanitize($pageTitle) . ' — ' : '' ?>Lab Peminjaman</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top">
  <div class="container-fluid px-4">
    <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>/dashboard.php">
      <i class="bi bi-hdd-network-fill me-2"></i>Lab Jaringan
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">

        <li class="nav-item">
          <a class="nav-link" href="<?= BASE_URL ?>/dashboard.php">
            <i class="bi bi-speedometer2 me-1"></i>Dashboard
          </a>
        </li>

        <?php if ($_SESSION['role'] === 'peminjam'): ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-box-arrow-in-right me-1"></i>Peminjaman
          </a>
          <ul class="dropdown-menu">
            <li>
              <a class="dropdown-item" href="<?= BASE_URL ?>/pages/peminjam/pinjam_barang.php">
                <i class="bi bi-box me-2 text-primary"></i>Pinjam Barang
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="<?= BASE_URL ?>/pages/peminjam/pinjam_ruangan.php">
                <i class="bi bi-door-open me-2 text-success"></i>Pinjam Ruangan
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item" href="<?= BASE_URL ?>/pages/peminjam/riwayat.php">
                <i class="bi bi-clock-history me-2 text-secondary"></i>Riwayat Saya
              </a>
            </li>
          </ul>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= BASE_URL ?>/pages/peminjam/stock.php">
            <i class="bi bi-stack me-1"></i>Data Stock
          </a>
        </li>
        <?php endif; ?>

        <?php if ($_SESSION['role'] === 'admin'): ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-clipboard-check me-1"></i>Kelola Peminjaman
          </a>
          <ul class="dropdown-menu">
            <li>
              <a class="dropdown-item" href="<?= BASE_URL ?>/pages/admin/peminjaman_barang.php">
                <i class="bi bi-box me-2 text-primary"></i>Peminjaman Barang
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="<?= BASE_URL ?>/pages/admin/peminjaman_ruangan.php">
                <i class="bi bi-door-open me-2 text-success"></i>Peminjaman Ruangan
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="<?= BASE_URL ?>/pages/admin/pengaduan.php">
                <i class="bi bi-exclamation-triangle me-2 text-danger"></i>Pengaduan
              </a>
            </li>
          </ul>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-database me-1"></i>Master Data
          </a>
          <ul class="dropdown-menu">
            <li>
              <a class="dropdown-item" href="<?= BASE_URL ?>/pages/admin/barang.php">
                <i class="bi bi-box-seam me-2 text-primary"></i>Data Barang
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="<?= BASE_URL ?>/pages/admin/ruangan.php">
                <i class="bi bi-building me-2 text-success"></i>Data Ruangan
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="<?= BASE_URL ?>/pages/admin/users.php">
                <i class="bi bi-people me-2 text-warning"></i>Data Pengguna
              </a>
            </li>
          </ul>
        </li>
        <?php endif; ?>

        <?php if ($_SESSION['role'] === 'kaprodi'): ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-clipboard-check me-1"></i>Approval
          </a>
          <ul class="dropdown-menu">
            <li>
              <a class="dropdown-item" href="<?= BASE_URL ?>/pages/kaprodi/peminjaman_barang.php">
                <i class="bi bi-box me-2 text-primary"></i>Peminjaman Barang
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="<?= BASE_URL ?>/pages/kaprodi/peminjaman_ruangan.php">
                <i class="bi bi-door-open me-2 text-success"></i>Peminjaman Ruangan
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="<?= BASE_URL ?>/pages/kaprodi/pengaduan.php">
                <i class="bi bi-exclamation-triangle me-2 text-danger"></i>Pengaduan Masalah
              </a>
            </li>
          </ul>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-graph-up me-1"></i>Laporan
          </a>
          <ul class="dropdown-menu">
            <li>
              <a class="dropdown-item" href="<?= BASE_URL ?>/pages/kaprodi/barang.php">
                <i class="bi bi-box-seam me-2 text-primary"></i>Inventaris Barang
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="<?= BASE_URL ?>/pages/kaprodi/ruangan.php">
                <i class="bi bi-building me-2 text-success"></i>Daftar Ruangan
              </a>
            </li>
          </ul>
        </li>
        <?php endif; ?>

      </ul>

      <ul class="navbar-nav">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-person-circle me-1"></i>
            <?= sanitize($_SESSION['nama']) ?>
            <span class="badge bg-light text-primary ms-1 fw-normal">
              <?= ucfirst($_SESSION['role']) ?>
            </span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li>
              <span class="dropdown-item-text text-muted small">
                <?= sanitize($_SESSION['jenis_identitas'] ?? '') ?>:
                <?= sanitize($_SESSION['nomor_identitas'] ?? '') ?>
              </span>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout.php">
                <i class="bi bi-box-arrow-right me-2"></i>Logout
              </a>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container-fluid px-4 py-4">
<?php showAlert(); ?>
