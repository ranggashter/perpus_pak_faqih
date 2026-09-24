<?php
/**
 * ============================================
 * FILE: user_edit.php
 * FUNGSI: Edit data user (admin only)
 * ============================================
 */

include '../config/auth.php';
include '../config/koneksi.php';

if (!hasRole('admin')) {
    header('Location: dashboard.php');
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header('Location: users.php');
    exit;
}

$stmt = mysqli_prepare($conn, 'SELECT id, nama, username, role FROM users WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user) {
    header('Location: users.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role = trim($_POST['role'] ?? 'petugas');

    if ($nama === '' || $username === '') {
        $error = 'Nama dan username wajib diisi!';
    } elseif (!in_array($role, ['admin', 'petugas'], true)) {
        $error = 'Role tidak valid!';
    } else {
        $cek = mysqli_prepare($conn, 'SELECT id FROM users WHERE username = ? AND id != ?');
        mysqli_stmt_bind_param($cek, 'si', $username, $id);
        mysqli_stmt_execute($cek);
        mysqli_stmt_store_result($cek);

        if (mysqli_stmt_num_rows($cek) > 0) {
            $error = 'Username <strong>' . htmlspecialchars($username) . '</strong> sudah dipakai user lain!';
        } else {
            if ($password !== '' || $confirmPassword !== '') {
                if (strlen($password) < 6) {
                    $error = 'Password minimal 6 karakter!';
                } elseif ($password !== $confirmPassword) {
                    $error = 'Konfirmasi password tidak cocok!';
                }
            }

            if ($error === '') {
                if ($password !== '') {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $upd = mysqli_prepare($conn, 'UPDATE users SET nama = ?, username = ?, password = ?, role = ? WHERE id = ?');
                    mysqli_stmt_bind_param($upd, 'ssssi', $nama, $username, $hash, $role, $id);
                } else {
                    $upd = mysqli_prepare($conn, 'UPDATE users SET nama = ?, username = ?, role = ? WHERE id = ?');
                    mysqli_stmt_bind_param($upd, 'sssi', $nama, $username, $role, $id);
                }

if (mysqli_stmt_execute($upd)) { 
    header("Location: users.php");
    exit;
} else {
                    $error = 'Gagal menyimpan: ' . mysqli_error($conn);
                }
                mysqli_stmt_close($upd);
            }
        }
        mysqli_stmt_close($cek);
    }
}

$role_label = ($_SESSION['role'] === 'admin') ? 'Admin' : 'Petugas';
$is_admin   = hasRole('admin');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Perpustakaan</title>
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
            max-width: 720px;
            margin: 0 auto;
        }

        /* ============ CARD ============ */
        .card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 28px 32px;
        }
        .card h2 {
            font-size: 17px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 4px;
        }
        .card .subtitle {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 24px;
        }

        /* ============ FORM ============ */
        .form-group {
            margin-bottom: 16px;
        }
        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 13px;
            outline: none;
            font-family: inherit;
            background: #ffffff;
            color: #374151;
        }
        .form-group input:focus,
        .form-group select:focus {
            border-color: #1e40af;
        }
        .form-group small {
            display: block;
            color: #9ca3af;
            font-size: 12px;
            margin-top: 4px;
        }
        .form-group .hint {
            color: #9ca3af;
            font-size: 12px;
            font-weight: 400;
            text-transform: none;
            letter-spacing: 0;
            margin-left: 4px;
        }

        /* ============ BUTTON ============ */
        .btn {
            padding: 9px 18px;
            border: 1px solid transparent;
            border-radius: 4px;
            color: #ffffff;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-family: inherit;
            transition: background 0.15s;
        }
        .btn-primary { background: #1e40af; }
        .btn-primary:hover { background: #1e3a8a; }

        .btn-secondary {
            background: #ffffff;
            color: #374151;
            border-color: #d1d5db;
        }
        .btn-secondary:hover { background: #f9fafb; }

        .form-actions {
            display: flex;
            gap: 8px;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #f3f4f6;
        }

        /* ============ ALERT ============ */
        .alert {
            padding: 11px 14px;
            border-radius: 4px;
            margin-bottom: 18px;
            font-size: 13px;
            border: 1px solid transparent;
        }
        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border-color: #fecaca;
        }
        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            border-color: #a7f3d0;
        }
        .alert a {
            color: inherit;
            font-weight: 600;
            text-decoration: underline;
            margin-left: 6px;
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
            .card {
                padding: 20px;
            }
            .form-actions {
                flex-direction: column;
            }
            .form-actions .btn {
                width: 100%;
                text-align: center;
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
            <<a href="users.php">user</a>
            <a href="tambah.php">Tambah Buku</a>
            <a href="../export/export_excel.php">Export Excel</a>
            <a href="../export/export_pdf.php">Export PDF</a>
            <a href="../auth/logout.php" onclick="return confirm('Yakin mau logout?')">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <h2>Edit User</h2>
            <p class="subtitle">ID User: <?= $user['id'] ?></p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?= $success ?>
                    <a href="users.php">Lihat User</a>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Nama Lengkap *</label>
                    <input type="text" name="nama"
                           value="<?= htmlspecialchars($_POST['nama'] ?? $user['nama']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Username *</label>
                    <input type="text" name="username"
                           value="<?= htmlspecialchars($_POST['username'] ?? $user['username']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Password <span class="hint">(kosongkan jika tidak ingin diubah)</span></label>
                    <input type="password" name="password" autocomplete="new-password">
                </div>

                <div class="form-group">
                    <label>Konfirmasi Password</label>
                    <input type="password" name="confirm_password" autocomplete="new-password">
                </div>

                <div class="form-group">
                    <label>Role *</label>
                    <select name="role">
                        <option value="admin" <?= (($_POST['role'] ?? $user['role']) === 'admin') ? 'selected' : '' ?>>Admin</option>
                        <option value="petugas" <?= (($_POST['role'] ?? $user['role']) === 'petugas') ? 'selected' : '' ?>>Petugas</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="users.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>