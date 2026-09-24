<?php
/**
 * ============================================
 * FILE: users.php
 * FUNGSI: Kelola data user (admin only)
 * ============================================
 */

include '../config/auth.php';
include '../config/koneksi.php';

if (!hasRole('admin')) {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Akses Ditolak</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
                background: #f5f6f8;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
                color: #1f2937;
                font-size: 14px;
            }
            .box {
                background: #ffffff;
                border: 1px solid #e5e7eb;
                border-radius: 6px;
                padding: 36px 32px;
                text-align: center;
                max-width: 420px;
                width: calc(100% - 32px);
            }
            .box h2 {
                color: #991b1b;
                font-size: 17px;
                font-weight: 600;
                margin-bottom: 8px;
            }
            .box p {
                color: #6b7280;
                font-size: 13px;
                margin-bottom: 22px;
            }
            .box a {
                display: inline-block;
                padding: 9px 18px;
                background: #1e40af;
                color: #ffffff;
                text-decoration: none;
                border-radius: 4px;
                font-size: 13px;
                font-weight: 500;
            }
            .box a:hover {
                background: #1e3a8a;
            }
        </style>
    </head>
    <body>
        <div class="box">
            <h2>Akses Ditolak</h2>
            <p>Hanya <strong>Admin</strong> yang bisa mengelola data user.</p>
            <a href="dashboard.php">Kembali ke Dashboard</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$result = mysqli_query($conn, "SELECT id, nama, username, role FROM users ORDER BY id ASC");

$role_label = ($_SESSION['role'] === 'admin') ? 'Admin' : 'Petugas';
$is_admin   = hasRole('admin');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - Perpustakaan</title>
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

        .role-badge {
            padding: 2px 10px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .role-admin   { background: #fef3c7; color: #92400e; }
        .role-petugas { background: #dbeafe; color: #1e40af; }

        /* ============ CONTAINER ============ */
        .container {
            padding: 24px 28px;
            max-width: 1200px;
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
            display: inline-block;
            font-family: inherit;
            transition: background 0.15s;
            white-space: nowrap;
        }
        .btn-primary { background: #1e40af; }
        .btn-primary:hover { background: #1e3a8a; }

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

        /* ============ BADGE ============ */
        .badge {
            display: inline-block;
            padding: 2px 9px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 500;
            text-transform: capitalize;
        }
        .badge-admin   { background: #fef3c7; color: #92400e; }
        .badge-petugas { background: #dbeafe; color: #1e40af; }

        /* ============ ACTION GROUP ============ */
        .action-group {
            display: flex;
            gap: 6px;
            justify-content: center;
        }

        /* ============ ALERT ============ */
        .alert {
            padding: 11px 14px;
            border-radius: 4px;
            margin-bottom: 18px;
            font-size: 13px;
            border: 1px solid transparent;
        }
        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            border-color: #a7f3d0;
        }
        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border-color: #fecaca;
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
            .table-box {
                overflow-x: auto;
            }
            table {
                min-width: 640px;
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
            <a href="data_buku.php">Data Buku</a>
            <a href="users.php" class="active">user</a>
            <a href="tambah.php">Tambah Buku</a>
            <a href="../export/export_excel.php">Export Excel</a>
            <a href="../export/export_pdf.php">Export PDF</a>
            <a href="../auth/logout.php" onclick="return confirm('Yakin mau logout?')">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="page-header">
            <h2>Kelola User</h2>
            <a href="user_tambah.php" class="btn btn-primary">+ Tambah User</a>
        </div>

        <?php if (isset($_SESSION['flash'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash']) ?></div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <div class="table-box">
            <table>
                <thead>
                    <tr>
                        <th style="width: 60px;">No</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th style="width: 110px;">Role</th>
                        <th style="width: 160px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php $no = 1; while ($user = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($user['nama']) ?></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td>
                                    <span class="badge <?= $user['role'] === 'admin' ? 'badge-admin' : 'badge-petugas' ?>">
                                        <?= htmlspecialchars($user['role']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-group">
                                        <a href="user_edit.php?id=<?= (int)$user['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                                        <?php if ((int)$user['id'] !== (int)$_SESSION['user_id']): ?>
                                            <a href="../api/user_hapus.php?id=<?= (int)$user['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus user ini?')">Hapus</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">Belum ada data user</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>