<?php
/**
 * ============================================
 * FILE: login.php
 * FUNGSI: Halaman login + proses autentikasi
 * ============================================
 */

session_start();
include '../config/koneksi.php';

// Kalau sudah login, langsung lempar ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: ../pages/dashboard.php");
    exit;
}

$error = "";

// --- Proses Login ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Validasi input tidak kosong
    if ($username === "" || $password === "") {
        $error = "Username dan password wajib diisi!";
    } else {
        // Pakai prepared statement biar aman dari SQL Injection
        $stmt = mysqli_prepare($conn, "SELECT id, nama, username, password, role FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            $storedPassword = $row['password'];
            $isLegacyPassword = password_get_info($storedPassword)['algo'] === 0;
            $passwordValid = $isLegacyPassword
                ? hash_equals($storedPassword, $password)
                : password_verify($password, $storedPassword);

            if ($passwordValid) {
                // Migrasi password lama ke hash setelah login berhasil.
                if ($isLegacyPassword) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $updateStmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
                    mysqli_stmt_bind_param($updateStmt, "si", $newHash, $row['id']);
                    mysqli_stmt_execute($updateStmt);
                    mysqli_stmt_close($updateStmt);
                } elseif (password_needs_rehash($storedPassword, PASSWORD_DEFAULT)) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $updateStmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
                    mysqli_stmt_bind_param($updateStmt, "si", $newHash, $row['id']);
                    mysqli_stmt_execute($updateStmt);
                    mysqli_stmt_close($updateStmt);
                }

                session_regenerate_id(true);
                $_SESSION['user_id']  = $row['id'];
                $_SESSION['nama']     = $row['nama'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role']     = $row['role'];

                header("Location: ../pages/dashboard.php");
                exit;
            }
        }

        $error = "Username atau password salah!";
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Perpustakaan</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            background: #f5f6f8;
            color: #1f2937;
            font-size: 14px;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        /* ============ LOGIN BOX ============ */
        .login-box {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 36px 32px;
            width: 100%;
            max-width: 380px;
        }
        .login-box h2 {
            font-size: 17px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 4px;
            text-align: center;
        }
        .login-box .subtitle {
            font-size: 13px;
            color: #6b7280;
            text-align: center;
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
        .form-group input {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 13px;
            outline: none;
            font-family: inherit;
            background: #ffffff;
            color: #374151;
            transition: border-color 0.15s;
        }
        .form-group input:focus {
            border-color: #1e40af;
        }

        /* ============ BUTTON ============ */
        .btn {
            width: 100%;
            padding: 10px 18px;
            border: 1px solid transparent;
            border-radius: 4px;
            color: #ffffff;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            font-family: inherit;
            transition: background 0.15s;
            background: #1e40af;
        }
        .btn:hover {
            background: #1e3a8a;
        }

        /* ============ ALERT ============ */
        .alert {
            padding: 11px 14px;
            border-radius: 4px;
            margin-bottom: 18px;
            font-size: 13px;
            border: 1px solid transparent;
            text-align: center;
        }
        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border-color: #fecaca;
        }

        /* ============ INFO DEMO ============ */
        .info {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: 12px 14px;
            font-size: 12px;
            color: #6b7280;
            margin-top: 20px;
            line-height: 1.7;
        }
        .info strong {
            color: #374151;
            font-weight: 600;
            display: block;
            margin-bottom: 4px;
        }
        .info code {
            background: #e5e7eb;
            padding: 1px 5px;
            border-radius: 3px;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 11px;
            color: #374151;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Login Perpustakaan</h2>
        <p class="subtitle">Masukkan username dan password Anda</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Masukkan username" required autofocus>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Masukkan password" required>
            </div>

            <button type="submit" class="btn">Login</button>
        </form>

        <div class="info">
            <strong>Akun Demo</strong>
            <code>admin</code> / <code>admin123</code> (Admin)<br>
            <code>petugas</code> / <code>petugas123</code> (Petugas)
        </div>
    </div>
</body>
</html>