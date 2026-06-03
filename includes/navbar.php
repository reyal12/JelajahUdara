<?php
if (!defined('BASE_URL')) {
    define('BASE_URL', '/JelajahUdara/');
}

$is_logged_in = isset($_SESSION['user_id']);
$user_role = $is_logged_in ? $_SESSION['user_role'] : null;
$user_name = $is_logged_in ? $_SESSION['user_name'] : '';
?>
<nav class="navbar navbar-expand-lg navbar-light sticky-top no-print">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="<?= BASE_URL ?>">
            <i class="fa-solid fa-plane-departure me-2 text-primary"></i>
            <span>Jelajah</span>Udara
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-link-item">
                    <a class="nav-link" href="<?= BASE_URL ?>">Home</a>
                </li>
                <li class="nav-link-item">
                    <a class="nav-link" href="<?= BASE_URL ?>customer/cari_tiket.php">Cari Tiket</a>
                </li>
                <li class="nav-link-item">
                    <a class="nav-link" href="<?= BASE_URL ?>#promo">Promo</a>
                </li>
                <li class="nav-link-item">
                    <a class="nav-link" href="<?= BASE_URL ?>#tentang-kami">Tentang Kami</a>
                </li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <?php if ($is_logged_in): ?>
                    <div class="dropdown">
                        <button class="btn btn-secondary dropdown-toggle d-flex align-items-center gap-2" type="button" id="userMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-circle-user fs-5"></i>
                            <span><?= htmlspecialchars($user_name) ?></span>
                            <span class="badge bg-info text-dark text-capitalize ms-1" style="font-size: 0.75rem;"><?= $user_role ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2" aria-labelledby="userMenuButton">
                            <?php if ($user_role === 'admin'): ?>
                                <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>admin/dashboard.php"><i class="fa-solid fa-gauge me-2 text-primary"></i> Dashboard Admin</a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>customer/dashboard.php"><i class="fa-solid fa-gauge me-2 text-primary"></i> Dashboard Saya</a></li>
                                <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>customer/riwayat.php"><i class="fa-solid fa-receipt me-2 text-primary"></i> Pesanan Saya</a></li>
                                <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>customer/profil.php"><i class="fa-solid fa-user-gear me-2 text-primary"></i> Edit Profil</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item py-2 text-danger" href="<?= BASE_URL ?>auth/logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i> Keluar</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>auth/login.php" class="btn btn-link text-decoration-none nav-link px-3">Masuk</a>
                    <a href="<?= BASE_URL ?>auth/register.php" class="btn btn-primary">Daftar</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
