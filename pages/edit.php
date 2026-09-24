<?php
/**
 * ============================================
 * FILE: edit.php
 * FUNGSI: Form + proses edit buku
 * Akses:  Semua role
 * ============================================
 */

include '../config/auth.php';
include '../config/koneksi.php';
include '../config/upload_buku.php';

$error   = "";
$success = "";

// --- Validasi ID ---
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header("Location: data_buku.php");
    exit;
}

// --- Ambil data buku yang mau diedit ---
$stmt = mysqli_prepare($conn, "SELECT * FROM books WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$buku   = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Kalau buku tidak ditemukan
if (!$buku) {
    header("Location: data_buku.php");
    exit;
}

// --- Proses Submit ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_buku    = trim($_POST['kode_buku']);
    $judul        = trim($_POST['judul']);
    $penulis      = trim($_POST['penulis']);
    $kategori     = trim($_POST['kategori']);
    $tahun_terbit = (int) $_POST['tahun_terbit'];
    $penerbit     = trim($_POST['penerbit']);
    $fotoBaru     = '';
    $hapusFoto    = isset($_POST['hapus_foto']);

    // Validasi
    if ($kode_buku === "" || $judul === "" || $penulis === "" ||
        $kategori === "" || $penerbit === "" || $tahun_terbit === 0) {
        $error = "Semua field wajib diisi!";
    } elseif ($tahun_terbit < 1900 || $tahun_terbit > (int)date('Y') + 1) {
        $error = "Tahun terbit tidak valid!";
    } else {
        $fotoBaru = uploadFotoBuku($_FILES['foto'] ?? [], $error);

        if ($fotoBaru === false) {
            $error = $error;
        } else {
        // Cek duplikat kode_buku (selain dirinya sendiri)
        $cek = mysqli_prepare($conn, "SELECT id FROM books WHERE kode_buku = ? AND id != ?");
        mysqli_stmt_bind_param($cek, "si", $kode_buku, $id);
        mysqli_stmt_execute($cek);
        mysqli_stmt_store_result($cek);

        if (mysqli_stmt_num_rows($cek) > 0) {
            $error = "Kode buku <strong>$kode_buku</strong> sudah dipakai buku lain!";
        } else {
            $fotoUntukSimpan = $fotoBaru !== '' ? $fotoBaru : ($hapusFoto ? '' : ($buku['foto'] ?? ''));
            $upd = mysqli_prepare($conn,
                "UPDATE books SET kode_buku = ?, judul = ?, penulis = ?,
                 kategori = ?, tahun_terbit = ?, penerbit = ?, foto = ? WHERE id = ?");
            mysqli_stmt_bind_param($upd, "ssssissi",
                $kode_buku, $judul, $penulis, $kategori, $tahun_terbit, $penerbit, $fotoUntukSimpan, $id);

            if (mysqli_stmt_execute($upd)) {
                if (($fotoBaru !== '' || $hapusFoto) && !empty($buku['foto'])) {
                    hapusFotoBuku($buku['foto']);
                }
                header("Location: data_buku.php");
    exit;
                // Update variabel $buku dengan data terbaru
                $buku = array_merge($buku, [
                    'kode_buku'    => $kode_buku,
                    'judul'        => $judul,
                    'penulis'      => $penulis,
                    'kategori'     => $kategori,
                    'tahun_terbit' => $tahun_terbit,
                    'penerbit'     => $penerbit,
                    'foto'         => $fotoUntukSimpan,
                ]);
            } else {
                hapusFotoBuku($fotoBaru);
                $error = "Gagal menyimpan: " . mysqli_error($conn);
            }
            mysqli_stmt_close($upd);
        }
        mysqli_stmt_close($cek);
        }
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
    <title>Edit Buku - Perpustakaan</title>
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
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="file"] {
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
        .form-group input[type="text"]:focus,
        .form-group input[type="number"]:focus {
            border-color: #1e40af;
        }
        .form-group small {
            display: block;
            color: #9ca3af;
            font-size: 12px;
            margin-top: 4px;
        }

        /* ============ FOTO PREVIEW ============ */
        .foto-preview {
            margin-bottom: 10px;
        }
        .foto-preview img {
            width: 90px;
            height: 120px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
        }
        .checkbox-line {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #374151;
            margin-bottom: 10px;
        }
        .checkbox-line input {
            width: auto;
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
            <a href="data_buku.php" class="active">Data Buku</a>
            <a href="users.php">user</a>
            <a href="tambah.php">Tambah Buku</a>
            <a href="../export/export_excel.php">Export Excel</a>
            <a href="../export/export_pdf.php">Export PDF</a>
            <a href="../auth/logout.php" onclick="return confirm('Yakin mau logout?')">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <h2>Edit Buku</h2>
            <p class="subtitle">ID Buku: <?= $buku['id'] ?></p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?= $success ?>
                    <a href="data_buku.php">Lihat Data</a>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Kode Buku *</label>
                    <input type="text" name="kode_buku"
                           value="<?= htmlspecialchars($buku['kode_buku']) ?>" required maxlength="20">
                </div>
                <div class="form-group">
                    <label>Judul Buku *</label>
                    <input type="text" name="judul"
                           value="<?= htmlspecialchars($buku['judul']) ?>" required maxlength="150">
                </div>
                <div class="form-group">
                    <label>Penulis *</label>
                    <input type="text" name="penulis"
                           value="<?= htmlspecialchars($buku['penulis']) ?>" required maxlength="100">
                </div>
                <div class="form-group">
                    <label>Kategori *</label>
                    <input type="text" name="kategori"
                           value="<?= htmlspecialchars($buku['kategori']) ?>" required maxlength="50">
                </div>
                <div class="form-group">
                    <label>Tahun Terbit *</label>
                    <input type="number" name="tahun_terbit" min="1900" max="<?= date('Y') + 1 ?>"
                           value="<?= htmlspecialchars($buku['tahun_terbit']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Penerbit *</label>
                    <input type="text" name="penerbit"
                           value="<?= htmlspecialchars($buku['penerbit']) ?>" required maxlength="100">
                </div>
                <div class="form-group">
                    <label>Foto Sampul (opsional)</label>
                    <?php if (!empty($buku['foto'])): ?>
                        <div class="foto-preview">
                            <img src="../uploads/buku/<?= rawurlencode($buku['foto']) ?>" alt="Sampul buku">
                        </div>
                        <label class="checkbox-line">
                            <input type="checkbox" name="hapus_foto" value="1">
                            Hapus foto saat disimpan
                        </label>
                    <?php endif; ?>
                    <input type="file" name="foto" accept="image/jpeg,image/png,image/webp">
                    <small>JPG, PNG, atau WEBP. Maksimal 2 MB.</small>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="data_buku.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>