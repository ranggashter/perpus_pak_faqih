<?php
/**
 * ============================================
 * FILE: koneksi.php
 * FUNGSI: Menghubungkan PHP ke database MySQL
 * ============================================
 */

// --- Konfigurasi Database ---
$host     = "localhost";   // Server MySQL (biasanya localhost)
$user     = "root";        // Username MySQL default XAMPP
$pass     = "";            // Password MySQL default XAMPP (kosong)
$db       = "perpus_api";  // Nama database yang sudah dibuat

// --- Proses Koneksi ---
$conn = mysqli_connect($host, $user, $pass, $db);

// --- Cek Koneksi ---
if (!$conn) {
    die("❌ Koneksi database gagal: " . mysqli_connect_error());
}

// --- Set charset biar aman dari masalah karakter ---
mysqli_set_charset($conn, "utf8mb4");

// --- (Opsional) Timezone ---
date_default_timezone_set('Asia/Jakarta');
?>