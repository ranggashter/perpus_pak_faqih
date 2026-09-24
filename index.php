<?php
/**
 * ============================================
 * FILE: index.php (root)
 * FUNGSI: Pintu masuk — redirect ke login/dashboard
 * ============================================
 */

session_start();

$basePath = '/perpus';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . $basePath . '/pages/dashboard.php');
} else {
    header('Location: ' . $basePath . '/auth/login.php');
}
exit;
?>