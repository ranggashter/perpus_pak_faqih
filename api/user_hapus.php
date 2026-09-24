<?php
/**
 * ============================================
 * FILE: user_hapus.php
 * FUNGSI: Hapus user (admin only)
 * ============================================
 */

session_start();
include '../config/auth.php';
include '../config/koneksi.php';

if (!hasRole('admin')) {
    header('Location: ../pages/dashboard.php');
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    $_SESSION['flash_error'] = 'ID user tidak valid!';
    header('Location: ../pages/users.php');
    exit;
}

if ((int) $_SESSION['user_id'] === $id) {
    $_SESSION['flash_error'] = 'Anda tidak bisa menghapus akun sendiri!';
    header('Location: ../pages/users.php');
    exit;
}

$cek = mysqli_prepare($conn, 'SELECT username FROM users WHERE id = ?');
mysqli_stmt_bind_param($cek, 'i', $id);
mysqli_stmt_execute($cek);
$result = mysqli_stmt_get_result($cek);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($cek);

if (!$user) {
    $_SESSION['flash_error'] = 'User tidak ditemukan!';
    header('Location: ../pages/users.php');
    exit;
}

$del = mysqli_prepare($conn, 'DELETE FROM users WHERE id = ?');
mysqli_stmt_bind_param($del, 'i', $id);

if (mysqli_stmt_execute($del)) {
    $_SESSION['flash'] = 'User "' . $user['username'] . '" berhasil dihapus.';
} else {
    $_SESSION['flash_error'] = 'Gagal menghapus user: ' . mysqli_error($conn);
}
mysqli_stmt_close($del);

header('Location: ../pages/users.php');
exit;
