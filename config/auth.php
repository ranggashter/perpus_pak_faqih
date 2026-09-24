<?php
/**
 * ============================================
 * FILE: auth.php
 * FUNGSI: Proteksi halaman — wajib login
 * Include file ini di paling atas halaman yang butuh login
 * ============================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$APP_BASE_PATH = '/perpus';

// Kalau belum login → tendang ke login.php
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $APP_BASE_PATH . '/auth/login.php');
    exit;
}

/**
 * Helper: cek apakah user yang login punya role tertentu
 * Contoh pemakaian: if (hasRole('admin')) { ... }
 */
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}
?>