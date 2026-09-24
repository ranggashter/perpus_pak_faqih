<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$isAdmin = function_exists('hasRole') && hasRole('admin');
?>
<nav class="navbar" aria-label="Navigasi utama">
    <a class="brand" href="dashboard.php">
        <span class="logo-icon">📚</span>
        <span>Perpustakaan</span>
    </a>
    <div class="menu">
        <a href="dashboard.php" class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">◈ Dashboard</a>
        <a href="data_buku.php" class="<?= $currentPage === 'data_buku.php' ? 'active' : '' ?>">▣ Data Buku</a>
        <a href="tambah.php" class="<?= $currentPage === 'tambah.php' ? 'active' : '' ?>">＋ Tambah Buku</a>
        <?php if ($isAdmin): ?>
            <a href="users.php" class="<?= in_array($currentPage, ['users.php', 'user_tambah.php', 'user_edit.php'], true) ? 'active' : '' ?>">♙ Kelola User</a>
        <?php endif; ?>
        <a href="../export/export_excel.php">↥ Excel</a>
        <a href="../export/export_pdf.php">↥ PDF</a>
        <a href="../auth/logout.php" class="logout" onclick="return confirm('Yakin mau logout?')">↪ Logout</a>
    </div>
</nav>
