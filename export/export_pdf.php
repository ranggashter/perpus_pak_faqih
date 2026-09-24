<?php
/**
 * ============================================
 * FILE: export_pdf.php
 * LOKASI: export/export_pdf.php
 * FUNGSI: Export data buku ke PDF pakai Dompdf
 * AKSES: Semua role (admin & petugas)
 * ============================================
 */

include '../config/auth.php';
include '../config/koneksi.php';
include '../config/filter_buku.php';

require '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// ============================================
// 1. Ambil data dari database dengan filter yang sama dengan tabel
// ============================================
$filter = buildBukuFilter();
$query  = "SELECT * FROM books" . $filter['where'] . " ORDER BY id ASC";

if (!empty($filter['params'])) {
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, $filter['types'], ...$filter['params']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, $query);
}

$buku_list = [];
while ($row = mysqli_fetch_assoc($result)) {
    $buku_list[] = $row;
}
$total_buku = count($buku_list);

// ============================================
// 2. Data untuk laporan
// ============================================
$tanggal_cetak = date('d F Y, H:i') . ' WIB';
$dicetak_oleh  = $_SESSION['nama'] . ' (' . ucfirst($_SESSION['role']) . ')';

// Array bulan Indonesia
$bulan_id = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
$tanggal_cetak = date('d') . ' ' . $bulan_id[(int)date('n')] . ' ' . date('Y, H:i') . ' WIB';

$filter_description = [];
if ($filter['search'] !== '') {
    $filter_description[] = 'Pencarian: ' . $filter['search'];
}
if ($filter['kategori'] !== '') {
    $filter_description[] = 'Kategori: ' . $filter['kategori'];
}
$filter_description = !empty($filter_description)
    ? implode(' | ', $filter_description)
    : 'Semua data';

// ============================================
// 3. Bangun HTML untuk PDF
// ============================================
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        /* KOP SURAT */
        .kop {
            border-bottom: 3px double #333;
            padding-bottom: 12px;
            margin-bottom: 15px;
            text-align: center;
        }
        .kop h1 {
            margin: 0;
            font-size: 18px;
            letter-spacing: 1px;
            color: #000;
        }
        .kop h2 {
            margin: 3px 0 0;
            font-size: 14px;
            font-weight: normal;
            color: #444;
        }
        .kop p {
            margin: 3px 0 0;
            font-size: 10px;
            color: #666;
        }

        /* JUDUL LAPORAN */
        .judul {
            text-align: center;
            margin: 20px 0 15px;
        }
        .judul h3 {
            margin: 0;
            font-size: 14px;
            text-decoration: underline;
            letter-spacing: 0.5px;
        }
        .judul p {
            margin: 5px 0 0;
            font-size: 10px;
            color: #666;
        }

        /* INFO */
        .info {
            margin-bottom: 12px;
            font-size: 10px;
        }
        .info table { width: 100%; }
        .info td { padding: 2px 0; vertical-align: top; }
        .info td:first-child { width: 100px; }

        /* TABEL DATA */
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        table.data th {
            background: #007bff;
            color: white;
            padding: 8px 6px;
            font-size: 10px;
            text-align: left;
            border: 1px solid #0056b3;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        table.data td {
            padding: 6px;
            border: 1px solid #ccc;
            font-size: 10px;
            vertical-align: top;
        }
        table.data tr:nth-child(even) td {
            background: #f8f9fa;
        }
        table.data td.center { text-align: center; }
        table.data td.right  { text-align: right; }

        /* TOTAL */
        .total {
            margin-top: 10px;
            text-align: right;
            font-size: 11px;
            font-weight: bold;
        }

        /* TANDA TANGAN */
        .ttd {
            margin-top: 40px;
            width: 100%;
        }
        .ttd table { width: 100%; }
        .ttd td {
            text-align: center;
            font-size: 10px;
            vertical-align: top;
            padding: 5px;
        }
        .ttd .nama {
            margin-top: 55px;
            font-weight: bold;
            text-decoration: underline;
        }

        /* FOOTER */
        .footer {
            position: fixed;
            bottom: -10px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #888;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
    </style>
</head>
<body>

    <!-- KOP -->
    <div class="kop">
        <h1>SMK PGRI 2 PONOROGO</h1>
        <h2>PERPUSTAKAAN SEKOLAH</h2>
        <p>Jl. Contoh Alamat No. 123, Ponorogo, Jawa Timur | Telp: (0352) 123456</p>
    </div>

    <!-- JUDUL -->
    <div class="judul">
        <h3>LAPORAN DATA BUKU PERPUSTAKAAN</h3>
        <p>Periode: s/d ' . $tanggal_cetak . '</p>
    </div>

    <!-- INFO -->
    <div class="info">
        <table>
            <tr>
                <td>Tanggal Cetak</td>
                <td>: ' . $tanggal_cetak . '</td>
            </tr>
            <tr>
                <td>Dicetak Oleh</td>
                <td>: ' . htmlspecialchars($dicetak_oleh, ENT_QUOTES, 'UTF-8') . '</td>
            </tr>
            <tr>
                <td>Filter</td>
                <td>: ' . htmlspecialchars($filter_description, ENT_QUOTES, 'UTF-8') . '</td>
            </tr>
        </table>
    </div>

    <!-- TABEL -->
    <table class="data">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 70px;">Kode</th>
                <th>Judul</th>
                <th style="width: 110px;">Penulis</th>
                <th style="width: 90px;">Kategori</th>
                <th style="width: 45px;">Tahun</th>
                <th style="width: 110px;">Penerbit</th>
            </tr>
        </thead>
        <tbody>';

if ($total_buku === 0) {
    $html .= '<tr><td colspan="7" class="center">Tidak ada data buku.</td></tr>';
} else {
    $no = 1;
    foreach ($buku_list as $b) {
        $html .= '<tr>
                    <td class="center">' . $no++ . '</td>
                    <td>' . htmlspecialchars($b['kode_buku']) . '</td>
                    <td>' . htmlspecialchars($b['judul']) . '</td>
                    <td>' . htmlspecialchars($b['penulis']) . '</td>
                    <td>' . htmlspecialchars($b['kategori']) . '</td>
                    <td class="center">' . $b['tahun_terbit'] . '</td>
                    <td>' . htmlspecialchars($b['penerbit']) . '</td>
                  </tr>';
    }
}

$html .= '
        </tbody>
    </table>

    <div class="total">
        Total Buku: ' . $total_buku . ' judul
    </div>

    <!-- TANDA TANGAN -->
    <div class="ttd">
        <table>
            <tr>
                <td style="width: 50%;"></td>
                <td style="width: 50%;">
                    Ponorogo, ' . $tanggal_cetak . '<br>
                    Kepala Perpustakaan,
                    <div class="nama">( .......................... )</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        Laporan ini dicetak otomatis dari Sistem Perpustakaan | Halaman <span class="pagenum"></span>
    </div>

</body>
</html>
';

// Tutup statement setelah selesai digunakan.
if (isset($stmt)) {
    mysqli_stmt_close($stmt);
}

// ============================================
// 4. Render PDF dengan Dompdf
// ============================================
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// ============================================
// 5. Output ke browser
// ============================================
$nama_file = 'laporan_data_buku_' . date('Y-m-d') . '.pdf';

// Attachment = true  → langsung download
// Attachment = false → tampil di browser (bisa diprint)
$dompdf->stream($nama_file, ['Attachment' => false]);

exit;
?>