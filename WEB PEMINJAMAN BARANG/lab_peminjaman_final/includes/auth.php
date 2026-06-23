<?php
// ============================================================
//  Auth & Helper Functions
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function requireRole(...$roles) {
    requireLogin();
    if (!in_array($_SESSION['role'], $roles)) {
        header('Location: ' . BASE_URL . '/dashboard.php');
        exit;
    }
}

function getRole() {
    return $_SESSION['role'] ?? '';
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function redirect($path) {
    header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
    exit;
}

function alert($msg, $type = 'success') {
    $_SESSION['alert'] = ['msg' => $msg, 'type' => $type];
}

function showAlert() {
    if (!empty($_SESSION['alert'])) {
        $a = $_SESSION['alert'];
        $class = match($a['type']) {
            'success' => 'alert-success',
            'danger'  => 'alert-danger',
            'warning' => 'alert-warning',
            default   => 'alert-info',
        };
        echo '<div class="alert ' . $class . ' alert-dismissible fade show" role="alert">
            ' . htmlspecialchars($a['msg'], ENT_QUOTES, 'UTF-8') . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
        unset($_SESSION['alert']);
    }
}

function sanitize($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

function uploadFile($file, $folder) {
    $uploadDir = UPLOAD_PATH . $folder . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($ext, $allowed)) return false;
    if ($file['size'] > 5 * 1024 * 1024) return false;
    $name = uniqid('', true) . '.' . $ext;
    if (move_uploaded_file($file['tmp_name'], $uploadDir . $name)) {
        return $folder . '/' . $name;
    }
    return false;
}

function badgeApproval($status) {
    $map = [
        'Waiting'  => 'warning text-dark',
        'Approved' => 'success',
        'Deny'     => 'danger',
    ];
    $c = $map[$status] ?? 'secondary';
    return '<span class="badge bg-' . $c . '">' . sanitize($status) . '</span>';
}

function badgeStatus($status) {
    $map = [
        'Tersedia' => 'success',
        'Dipakai'  => 'primary',
        'Rusak'    => 'danger',
    ];
    $c = $map[$status] ?? 'secondary';
    return '<span class="badge bg-' . $c . '">' . sanitize($status) . '</span>';
}

function priorityLabel($p) {
    $map = [
        1 => '<span class="badge bg-secondary">Rendah</span>',
        2 => '<span class="badge bg-warning text-dark">Sedang</span>',
        3 => '<span class="badge bg-danger">Tinggi</span>',
    ];
    return $map[(int)$p] ?? '-';
}
