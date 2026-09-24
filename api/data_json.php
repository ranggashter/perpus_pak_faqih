<?php
/**
 * ============================================
 * FILE: data_json.php
 * FUNGSI: API JSON — mengeluarkan data buku
 * Akses:  http://localhost/perpus_api/data_json.php
 * ============================================
 */

// --- Header: kasih tau browser bahwa ini JSON ---
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');        // izinkan fetch dari origin manapun (untuk praktik)
header('Access-Control-Allow-Methods: GET');     // hanya izinkan method GET

// --- Koneksi database dan filter bersama ---
include '../config/koneksi.php';
include '../config/filter_buku.php';

// --- Ambil parameter ---
$filter = buildBukuFilter();
$where  = $filter['where'];
$params = $filter['params'];
$types  = $filter['types'];
$page     = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$per_page = 10;

// --- Hitung total data hasil filter ---
$count_sql = "SELECT COUNT(*) AS total FROM books" . $where;

if (!empty($params)) {
    $count_stmt = mysqli_prepare($conn, $count_sql);
    mysqli_stmt_bind_param($count_stmt, $types, ...$params);
    mysqli_stmt_execute($count_stmt);
    $count_result = mysqli_stmt_get_result($count_stmt);
} else {
    $count_result = mysqli_query($conn, $count_sql);
}

$count_row     = mysqli_fetch_assoc($count_result);
$total         = (int) ($count_row['total'] ?? 0);
mysqli_free_result($count_result);

if (isset($count_stmt)) {
    mysqli_stmt_close($count_stmt);
}

// Pastikan halaman yang diminta tidak melewati jumlah halaman yang tersedia.
$total_pages = max(1, (int) ceil($total / $per_page));
if ($page > $total_pages) {
    $page = $total_pages;
}
$offset = ($page - 1) * $per_page;

// --- Ambil data untuk halaman ini ---
$sql = "SELECT id, kode_buku, judul, penulis, kategori, tahun_terbit, penerbit, foto
        FROM books" . $where . " ORDER BY id ASC LIMIT $per_page OFFSET $offset";

if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, $sql);
}

// --- Bungkus hasil ke array ---
$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Konversi tipe data biar rapi di JSON
    $row['id']           = (int) $row['id'];
    $row['tahun_terbit'] = (int) $row['tahun_terbit'];
    $data[] = $row;
}

if (isset($stmt)) {
    mysqli_stmt_close($stmt);
}

// --- Output JSON ---
echo json_encode([
    'status'      => 'success',
    'total'       => $total,
    'page'        => $page,
    'per_page'    => $per_page,
    'total_pages' => $total_pages,
    'data'        => $data
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

// --- Tutup koneksi ---
mysqli_close($conn);
?>