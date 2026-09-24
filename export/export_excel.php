<?php
/**
 * ============================================
 * FILE: export_excel.php
 * LOKASI: export/export_excel.php
 * FUNGSI: Export data buku ke file Excel (CSV)
 * AKSES: Semua role (admin & petugas)
 * ============================================
 */

include '../config/auth.php';
include '../config/koneksi.php';    // Koneksi database
include '../config/filter_buku.php';

$filter = buildBukuFilter();

// --- Nama file dengan tanggal ---
$nama_file = 'data_buku_' . date('Y-m-d') . '.csv';

// --- Header HTTP untuk download file ---
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $nama_file . '"');
header('Pragma: no-cache');
header('Expires: 0');

// --- Buka output stream ---
$output = fopen('php://output', 'w');

// --- Tambahkan BOM UTF-8 supaya Excel membaca karakter dengan benar ---
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// --- Baris Judul Laporan ---
fputcsv($output, ['LAPORAN DATA BUKU PERPUSTAKAAN']);
fputcsv($output, ['Tanggal Export: ' . date('d/m/Y H:i') . ' WIB']);
fputcsv($output, ['Diekspor oleh: ' . $_SESSION['nama'] . ' (' . $_SESSION['role'] . ')']);

$filter_description = [];
if ($filter['search'] !== '') {
    $filter_description[] = 'Pencarian: ' . $filter['search'];
}
if ($filter['kategori'] !== '') {
    $filter_description[] = 'Kategori: ' . $filter['kategori'];
}
fputcsv($output, [
    'Filter: ' . (!empty($filter_description) ? implode(' | ', $filter_description) : 'Semua data')
]);
fputcsv($output, []); // baris kosong

// --- Header Kolom ---
fputcsv($output, [
    'NO',
    'KODE BUKU',
    'JUDUL',
    'PENULIS',
    'KATEGORI',
    'TAHUN TERBIT',
    'PENERBIT'
]);

// --- Ambil data dari database dengan filter yang sama dengan tabel ---
$query = "SELECT kode_buku, judul, penulis, kategori, tahun_terbit, penerbit
          FROM books" . $filter['where'] . "
          ORDER BY id ASC";

if (!empty($filter['params'])) {
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, $filter['types'], ...$filter['params']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, $query);
}

$no = 1;
while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, [
        $no++,
        $row['kode_buku'],
        $row['judul'],
        $row['penulis'],
        $row['kategori'],
        $row['tahun_terbit'],
        $row['penerbit']
    ]);
}

// --- Baris Total ---
fputcsv($output, []);
fputcsv($output, ['TOTAL BUKU:', $no - 1]);

// --- Tutup stream dan statement ---
if (isset($stmt)) {
    mysqli_stmt_close($stmt);
}
fclose($output);
exit;
?>