<?php
/**
 * ============================================
 * FILE: dashboard.php
 * FUNGSI: Halaman utama admin/petugas + statistik
 * ============================================
 */

include '../config/auth.php';
include '../config/koneksi.php';
// --- Ambil Statistik dari Database ---

// 1. Total Buku
$q1 = mysqli_query($conn, "SELECT COUNT(*) AS total FROM books");
$total_buku = mysqli_fetch_assoc($q1)['total'];

// 2. Jumlah Kategori (unik)
$q2 = mysqli_query($conn, "SELECT COUNT(DISTINCT kategori) AS total FROM books");
$total_kategori = mysqli_fetch_assoc($q2)['total'];

// 3. Jumlah Penulis (unik)
$q3 = mysqli_query($conn, "SELECT COUNT(DISTINCT penulis) AS total FROM books");
$total_penulis = mysqli_fetch_assoc($q3)['total'];

// 4. Tahun Terbit Terbaru
$q4 = mysqli_query($conn, "SELECT MAX(tahun_terbit) AS max_tahun FROM books");
$tahun_terbaru = mysqli_fetch_assoc($q4)['max_tahun'];

// 5. Statistik tambahan: jumlah user (khusus admin)
$total_user = 0;
if (hasRole('admin')) {
    $q5 = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users");
    $total_user = mysqli_fetch_assoc($q5)['total'];
}

// 6. Daftar 5 buku terbaru (untuk tabel "Buku Terbaru")
$q6 = mysqli_query($conn, "SELECT id, kode_buku, judul, penulis, kategori, tahun_terbit, foto
                         FROM books
                         ORDER BY id DESC
                         LIMIT 5");

// --- Label Role untuk Tampilan ---
$role_label = ($_SESSION['role'] === 'admin') ? 'Admin' : 'Petugas';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Perpustakaan</title>
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

        /* ============ CONTAINER ============ */
        .container {
            padding: 24px 28px;
            max-width: 1180px;
            margin: 0 auto;
        }

        /* ============ WELCOME ============ */
        .welcome {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 20px 24px;
            margin-bottom: 24px;
        }
        .welcome h2 {
            font-size: 17px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 4px;
        }
        .welcome p {
            font-size: 13px;
            color: #6b7280;
        }
.role-badge {
    padding: 2px 10px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.role-admin   { background: #dbeafe; color: #1e40af; } 
.role-petugas { background: #fef3c7; color: #92400e; }

        /* ============ SECTION TITLE ============ */
        .section-title {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 12px;
        }

        /* ============ STATS ============ */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
            margin-bottom: 28px;
        }
        .card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 18px 20px;
        }
        .card h3 {
            font-size: 11px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .card .value {
            font-size: 26px;
            font-weight: 600;
            color: #1e40af;
            letter-spacing: -0.01em;
        }

        /* ============ TABLE ============ */
        .table-box {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 28px;
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
        }
        td {
            border-bottom: 1px solid #f3f4f6;
            color: #374151;
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

        /* ============ EMPTY STATE ============ */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #9ca3af;
            font-size: 13px;
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
            .stats {
                grid-template-columns: repeat(2, 1fr);
            }
            .table-box {
                overflow-x: auto;
            }
            table {
                min-width: 600px;
            }
        }

        @media (max-width: 480px) {
            .stats {
                grid-template-columns: 1fr;
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
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="data_buku.php">Data Buku</a>
        <a href="users.php">user</a>
        <a href="tambah.php">Tambah Buku</a>
        <a href="../export/export_excel.php">Export Excel</a>
        <a href="../export/export_pdf.php">Export PDF</a>
        <a href="../auth/logout.php" onclick="return confirm('Yakin mau logout?')">Logout</a>
    </div>
</div>

    <div class="container">

        <!-- Welcome -->
        <div class="welcome">
            <h2>
                Halo, <?= htmlspecialchars($_SESSION['nama']) ?>
                <span class="role-badge <?= $_SESSION['role'] === 'admin' ? 'role-admin' : 'role-petugas' ?>">
                    <?= $role_label ?>
                </span>
            </h2>
            <p>Selamat datang di Sistem Data Buku Perpustakaan. Berikut ringkasan data hari ini.</p>
        </div>

        <!-- Statistik -->
        <div class="section-title">Statistik Data Buku</div>
        <div class="stats">
            <div class="card">
                <h3>Total Buku</h3>
                <div class="value"><?= number_format($total_buku, 0, ',', '.') ?></div>
            </div>
            <div class="card">
                <h3>Jumlah Kategori</h3>
                <div class="value"><?= number_format($total_kategori, 0, ',', '.') ?></div>
            </div>
            <div class="card">
                <h3>Jumlah Penulis</h3>
                <div class="value"><?= number_format($total_penulis, 0, ',', '.') ?></div>
            </div>
            <div class="card">
                <h3>Tahun Terbit Terbaru</h3>
                <div class="value"><?= $tahun_terbaru ?: '-' ?></div>
            </div>
            <?php if (hasRole('admin')): ?>
            <div class="card">
                <h3>Total User</h3>
                <div class="value"><?= number_format($total_user, 0, ',', '.') ?></div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Buku Terbaru -->
        <div class="section-title">5 Buku Terbaru</div>
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
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($q6) > 0): ?>
                        <?php $no = 1; while ($buku = mysqli_fetch_assoc($q6)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <?php
                                $foto = trim((string) ($buku['foto'] ?? ''));
                                $foto_url = $foto !== '' ? '../uploads/buku/' . rawurlencode($foto) : '';
                                ?>
                                <?php if ($foto_url !== ''): ?>
                                    <img class="book-thumb"
                                         src="<?= htmlspecialchars($foto_url, ENT_QUOTES, 'UTF-8') ?>"
                                         alt="Sampul <?= htmlspecialchars((string) ($buku['judul'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                <?php else: ?>
                                    <span class="book-thumb book-thumb-empty">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($buku['kode_buku']) ?></td>
                            <td><?= htmlspecialchars($buku['judul']) ?></td>
                            <td><?= htmlspecialchars($buku['penulis']) ?></td>
                            <td><?= htmlspecialchars($buku['kategori']) ?></td>
                            <td><?= $buku['tahun_terbit'] ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">Belum ada data buku</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>