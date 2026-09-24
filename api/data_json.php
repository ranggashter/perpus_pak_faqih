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

// --- Koneksi database ---
include '../config/koneksi.php';

// --- Ambil parameter (opsional, untuk fitur tambahan) ---
// Contoh: ?kategori=Novel  → filter berdasarkan kategori
$kategori = isset($_GET['kategori']) ? trim($_GET['kategori']) : '';
$search   = isset($_GET['search'])   ? trim($_GET['search'])   : '';

// --- Susun query dasar ---
$sql = "SELECT id, kode_buku, judul, penulis, kategori, tahun_terbit, penerbit, foto
        FROM books 
        WHERE 1=1";

// --- Tambahkan filter kalau ada parameter ---
$params = [];
$types  = "";

if ($kategori !== "") {
    $sql .= " AND kategori = ?";
    $params[] = $kategori;
    $types   .= "s";
}

if ($search !== "") {
    $sql .= " AND (judul LIKE ? OR penulis LIKE ? OR kode_buku LIKE ?)";
    $like = "%" . $search . "%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types   .= "sss";
}

$sql .= " ORDER BY id ASC";

// --- Eksekusi query (prepared statement kalau ada filter) ---
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

// --- Output JSON ---
echo json_encode([
    'status'  => 'success',
    'total'   => count($data),
    'data'    => $data
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

// --- Tutup koneksi ---
mysqli_close($conn);
?>