<?php
/**
 * ============================================
 * FILE: logout.php
 * FUNGSI: Hancurkan session & kembali ke login
 * ============================================
 */

session_start();

// Hapus semua data session
$_SESSION = [];

// Hapus cookie session juga (biar benar-benar logout)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Hancurkan session
session_destroy();

// Redirect ke halaman login
header("Location: login.php");
exit;
?>