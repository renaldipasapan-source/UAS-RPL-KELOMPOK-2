<?php
// ============================================================
//  Konfigurasi Database
//  Sesuaikan dengan setting Laragon Anda
// ============================================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'lab_peminjaman_final');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$conn->set_charset('utf8mb4');

if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;padding:40px;max-width:600px;margin:60px auto;border:1px solid #e74c3c;border-radius:8px;color:#c0392b;">
        <h2>&#9888; Koneksi Database Gagal</h2>
        <p><strong>Error:</strong> ' . $conn->connect_error . '</p>
        <hr>
        <p>Langkah perbaikan:</p>
        <ol>
          <li>Pastikan <strong>MySQL</strong> sudah berjalan di Laragon</li>
          <li>Import file <code>lab_peminjaman_final.sql</code> ke phpMyAdmin</li>
          <li>Sesuaikan <code>DB_USER</code> dan <code>DB_PASS</code> di file <code>includes/db.php</code></li>
        </ol>
    </div>');
}

// Base URL — sesuaikan nama folder jika berbeda
define('BASE_URL',    '/lab_peminjaman_final');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_URL',  BASE_URL . '/uploads/');
