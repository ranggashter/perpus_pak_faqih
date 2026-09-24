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

// --- Ambil data dari database ---
$query = "SELECT kode_buku, judul, penulis, kategori, tahun_terbit, penerbit 
          FROM books 
          ORDER BY id ASC";
$result = mysqli_query($conn, $query);

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

// --- Tutup stream ---
fclose($output);
exit;
?>