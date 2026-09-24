<?php
/**
 * ============================================
 * FILE: hapus.php
 * FUNGSI: Proses hapus buku
 * Akses:  HANYA ADMIN
 * ============================================
 */

include '../config/auth.php';
include '../config/koneksi.php';
include '../config/upload_buku.php';

// ============================================
// 🔒 PROTEKSI ROLE DI SISI SERVER
// Petugas tidak boleh hapus, walaupun akses URL langsung
// ============================================
if (!hasRole('admin')) {
    // Tampilkan halaman akses ditolak
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Akses Ditolak</title>
        <style>
            body {
                font-family: Arial, sans-serif; background: #f0f2f5;
                display: flex; justify-content: center; align-items: center;
                height: 100vh; margin: 0;
            }
            .box {
                background: white; padding: 40px; border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center;
                max-width: 400px;
            }
            h2 { color: #dc3545; margin-bottom: 15px; }
            p { color: #555; margin-bottom: 20px; }
            a {
                display: inline-block; padding: 10px 20px; background: #007bff;
                color: white; text-decoration: none; border-radius: 6px;
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <div class="box">
            <h2>🚫 Akses Ditolak</h2>
            <p>Hanya <strong>Admin</strong> yang boleh menghapus data buku.</p>
            <a href="data_buku.php">← Kembali ke Data Buku</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// --- Validasi ID ---
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header("Location: data_buku.php");
    exit;
}

// --- Cek buku ada atau tidak ---
$cek = mysqli_prepare($conn, "SELECT judul, foto FROM books WHERE id = ?");
mysqli_stmt_bind_param($cek, "i", $id);
mysqli_stmt_execute($cek);
$result = mysqli_stmt_get_result($cek);
$buku = mysqli_fetch_assoc($result);
mysqli_stmt_close($cek);

if (!$buku) {
    // Buku tidak ditemukan
    header("Location: ../pages/data_buku.php");
    exit;
}

// --- Hapus data ---
$del = mysqli_prepare($conn, "DELETE FROM books WHERE id = ?");
mysqli_stmt_bind_param($del, "i", $id);

if (mysqli_stmt_execute($del)) {
    hapusFotoBuku($buku['foto'] ?? '');
    // Berhasil → kembali ke daftar dengan pesan
    $_SESSION['flash'] = "Buku \"{$buku['judul']}\" berhasil dihapus.";
} else {
    $_SESSION['flash_error'] = "Gagal menghapus: " . mysqli_error($conn);
}
mysqli_stmt_close($del);

header("Location: ../pages/data_buku.php");
exit;
?>