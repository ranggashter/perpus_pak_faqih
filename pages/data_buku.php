<?php
/**
 * ============================================
 * FILE: data_buku.php
 * FUNGSI: Tampilkan data buku via Fetch API
 * ============================================
 */

include '../config/auth.php';
include '../config/koneksi.php';   // Koneksi database (buat dropdown kategori)
$role_label = ($_SESSION['role'] === 'admin') ? 'Admin' : 'Petugas';
$is_admin   = hasRole('admin');

// --- Ambil daftar kategori unik untuk dropdown filter ---
$q_kat = mysqli_query($conn, "SELECT DISTINCT kategori FROM books ORDER BY kategori ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Buku - Perpustakaan</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            background: #f5f6f8;
            color: #1f2937;
            font-size: 14px;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        /* ============ NAVBAR ============ */
        .navbar {
            background: #1e40af;
            padding: 0 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 56px;
        }
        .navbar .brand {
            color: #ffffff;
            font-weight: 600;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .navbar .menu {
            display: flex;
            align-items: center;
            gap: 2px;
        }
        .navbar .menu a {
            color: #cbd5e1;
            text-decoration: none;
            font-size: 13px;
            padding: 7px 12px;
            border-radius: 4px;
            transition: background 0.15s, color 0.15s;
        }
        .navbar .menu a:hover {
            background: #1e3a8a;
            color: #ffffff;
        }
        .navbar .menu a.active {
            background: #1e3a8a;
            color: #ffffff;
            font-weight: 500;
        }

        /* Role badge di navbar */
        .role-badge {
            padding: 2px 10px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .role-admin {
            background: #fef3c7;
            color: #92400e;
        }
        .role-petugas {
            background: #dbeafe;
            color: #1e40af;
        }

        /* ============ CONTAINER ============ */
        .container {
            padding: 24px 28px;
            max-width: 1280px;
            margin: 0 auto;
        }

        /* ============ PAGE HEADER ============ */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 12px;
        }
        .page-header h2 {
            font-size: 17px;
            font-weight: 600;
            color: #111827;
        }

        /* ============ TOOLBAR ============ */
        .toolbar {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 14px 18px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: 16px;
        }
        .toolbar input,
        .toolbar select {
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 13px;
            outline: none;
            font-family: inherit;
            background: #ffffff;
            color: #374151;
        }
        .toolbar input:focus,
        .toolbar select:focus {
            border-color: #1e40af;
        }
        .toolbar input[type="text"] {
            flex: 1;
            min-width: 200px;
        }
        .toolbar .spacer {
            flex: 1;
        }

        /* ============ BUTTON ============ */
        .btn {
            padding: 8px 16px;
            border: 1px solid transparent;
            border-radius: 4px;
            color: #ffffff;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.15s;
            display: inline-block;
            font-family: inherit;
            white-space: nowrap;
        }
        .btn-primary { background: #1e40af; }
        .btn-primary:hover { background: #1e3a8a; }

        .btn-success { background: #047857; }
        .btn-success:hover { background: #065f46; }

        .btn-secondary {
            background: #ffffff;
            color: #374151;
            border-color: #d1d5db;
        }
        .btn-secondary:hover { background: #f9fafb; }

        .btn-danger { background: #b91c1c; }
        .btn-danger:hover { background: #991b1b; }

        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }

        /* ============ INFO TOTAL ============ */
        .info-total {
            margin-bottom: 12px;
            color: #6b7280;
            font-size: 13px;
        }
        .info-total strong {
            color: #1e40af;
            font-weight: 600;
        }

        /* ============ TABLE ============ */
        .table-box {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            overflow: hidden;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 11px 18px;
            text-align: left;
            font-size: 13px;
        }
        th {
            background: #f9fafb;
            color: #6b7280;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
            border-bottom: 1px solid #e5e7eb;
            white-space: nowrap;
        }
        td {
            border-bottom: 1px solid #f3f4f6;
            color: #374151;
            vertical-align: middle;
        }
        tbody tr:last-child td {
            border-bottom: none;
        }
        tbody tr:hover td {
            background: #f9fafb;
        }

        /* ============ BOOK THUMB ============ */
        .book-thumb {
            width: 44px;
            height: 58px;
            object-fit: cover;
            border-radius: 3px;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
            display: block;
        }
        .book-thumb-empty {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            font-size: 11px;
        }

        /* ============ BADGE KATEGORI ============ */
        .badge {
            display: inline-block;
            padding: 2px 9px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 500;
            background: #dbeafe;
            color: #1e40af;
        }

        /* ============ LOADING & EMPTY ============ */
        .loading, .empty {
            text-align: center;
            padding: 40px 20px;
            color: #9ca3af;
            font-size: 13px;
        }
        .empty-error {
            color: #b91c1c;
        }

        /* ============ ACTION BUTTONS IN TABLE ============ */
        .action-group {
            display: flex;
            gap: 6px;
            justify-content: center;
        }

        /* ============ RESPONSIVE ============ */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                height: auto;
                padding: 12px 16px;
                gap: 10px;
            }
            .navbar .menu {
                flex-wrap: wrap;
                justify-content: center;
            }
            .container {
                padding: 16px;
            }
            .table-box {
                overflow-x: auto;
            }
            table {
                min-width: 900px;
            }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <div class="navbar">
        <div class="brand">
            Perpustakaan Admin
            <span class="role-badge <?= $is_admin ? 'role-admin' : 'role-petugas' ?>">
                <?= $role_label ?>
            </span>
        </div>
        <div class="menu">
            <a href="dashboard.php">Dashboard</a>
            <a href="data_buku.php" class="active">Data Buku</a>
            <a href="users.php">user</a>
            <a href="tambah.php">Tambah Buku</a>
            <a href="../export/export_excel.php">Export Excel</a>
            <a href="../export/export_pdf.php">Export PDF</a>
            <a href="../auth/logout.php" onclick="return confirm('Yakin mau logout?')">Logout</a>
        </div>
    </div>

    <div class="container">

        <!-- Header -->
        <div class="page-header">
            <h2>Data Buku</h2>
            <a href="tambah.php" class="btn btn-success">+ Tambah Buku</a>
        </div>

        <!-- Toolbar -->
        <div class="toolbar">
            <input type="text" id="inputSearch" placeholder="Cari judul / penulis / kode...">
            <select id="filterKategori">
                <option value="">Semua Kategori</option>
                <?php while ($k = mysqli_fetch_assoc($q_kat)): ?>
                    <option value="<?= htmlspecialchars($k['kategori']) ?>">
                        <?= htmlspecialchars($k['kategori']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button class="btn btn-primary" onclick="loadData()">Cari</button>
            <button class="btn btn-secondary" onclick="resetFilter()">Reset</button>
            <div class="spacer"></div>
            <a href="../export/export_excel.php" class="btn btn-secondary">Export Excel</a>
            <a href="../export/export_pdf.php" class="btn btn-secondary">Export PDF</a>
        </div>

        <!-- Info total -->
        <div class="info-total">
            Total: <strong id="totalData">0</strong> buku
        </div>

        <!-- Tabel -->
        <div class="table-box">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th style="width: 60px;">Foto</th>
                        <th>Kode</th>
                        <th>Judul</th>
                        <th>Penulis</th>
                        <th>Kategori</th>
                        <th>Tahun</th>
                        <th>Penerbit</th>
                        <th style="width: 140px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tbodyBuku">
                    <tr><td colspan="9" class="loading">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>

    </div>

    <script>
    // ============================================
    // KONFIGURASI
    // ============================================
    const API_URL   = '../api/data_json.php';
    const IS_ADMIN  = <?= $is_admin ? 'true' : 'false' ?>;

    // ============================================
    // FUNGSI: Ambil & tampilkan data
    // ============================================
    function loadData() {
        const search   = document.getElementById('inputSearch').value.trim();
        const kategori = document.getElementById('filterKategori').value;

        // Bangun URL dengan parameter
        let url = API_URL;
        const params = [];
        if (search)   params.push('search='   + encodeURIComponent(search));
        if (kategori) params.push('kategori=' + encodeURIComponent(kategori));
        if (params.length > 0) url += '?' + params.join('&');

        // Tampilkan loading
        const tbody = document.getElementById('tbodyBuku');
        tbody.innerHTML = '<tr><td colspan="9" class="loading">Memuat data...</td></tr>';

        // Fetch API
        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error('HTTP ' + response.status);
                return response.json();
            })
            .then(result => {
                const data = result.data || [];
                document.getElementById('totalData').textContent = result.total || 0;

                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="9" class="empty">Tidak ada data ditemukan</td></tr>';
                    return;
                }

                // Render baris
                let html = '';
                data.forEach((buku, index) => {
                    const fotoUrl = buku.foto
                        ? '../uploads/buku/' + encodeURIComponent(buku.foto)
                        : '';
                    const fotoHtml = fotoUrl
                        ? `<img class="book-thumb" src="${fotoUrl}" alt="Sampul ${escapeHtml(buku.judul)}">`
                        : '<span class="book-thumb book-thumb-empty">-</span>';

                    // Tombol hapus hanya untuk admin
                    const tombolHapus = IS_ADMIN
                        ? `<a href="hapus.php?id=${buku.id}" class="btn btn-danger btn-sm"
                              onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">Hapus</a>`
                        : '';

                    html += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${fotoHtml}</td>
                            <td><strong>${escapeHtml(buku.kode_buku)}</strong></td>
                            <td>${escapeHtml(buku.judul)}</td>
                            <td>${escapeHtml(buku.penulis)}</td>
                            <td><span class="badge">${escapeHtml(buku.kategori)}</span></td>
                            <td>${buku.tahun_terbit}</td>
                            <td>${escapeHtml(buku.penerbit)}</td>
                            <td>
                                <div class="action-group">
                                    <a href="edit.php?id=${buku.id}" class="btn btn-secondary btn-sm">Edit</a>
                                    ${tombolHapus}
                                </div>
                            </td>
                        </tr>
                    `;
                });
                tbody.innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                tbody.innerHTML = '<tr><td colspan="9" class="empty empty-error">Gagal memuat data: ' + error.message + '</td></tr>';
            });
    }

    // ============================================
    // FUNGSI: Reset filter
    // ============================================
    function resetFilter() {
        document.getElementById('inputSearch').value = '';
        document.getElementById('filterKategori').value = '';
        loadData();
    }

    // ============================================
    // FUNGSI: Cegah XSS (escape karakter HTML)
    // ============================================
    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    // ============================================
    // AUTO LOAD saat halaman dibuka
    // ============================================
    document.addEventListener('DOMContentLoaded', loadData);

    // ============================================
    // Enter di kolom search → langsung cari
    // ============================================
    document.getElementById('inputSearch').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') loadData();
    });
    </script>

</body>
</html>                     