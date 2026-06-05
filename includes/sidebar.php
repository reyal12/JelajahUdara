<?php
if (!defined('BASE_URL')) {
    define('BASE_URL', '/JelajahUdara/');
}

$current_page = basename($_SERVER['SCRIPT_NAME']);
$current_dir = basename(dirname($_SERVER['SCRIPT_NAME']));
?>
<aside class="sidebar no-print">
    <div class="sidebar-brand d-flex align-items-center justify-content-between">
        <a href="<?= BASE_URL ?>" class="text-white text-decoration-none d-flex align-items-center">
            <i class="fa-solid fa-plane-departure text-info me-2"></i>
            <span>Jelajah<strong class="text-info">Udara</strong></span>
        </a>
    </div>
    
    <ul class="sidebar-menu">
        <li class="sidebar-item <?= ($current_page == 'dashboard.php' && $current_dir == 'admin') ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>admin/dashboard.php">
                <i class="fa-solid fa-chart-line"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="sidebar-item <?= ($current_dir == 'maskapai') ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>admin/maskapai/index.php">
                <i class="fa-solid fa-plane"></i>
                <span>Kelola Maskapai</span>
            </a>
        </li>
        <li class="sidebar-item <?= ($current_dir == 'bandara') ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>admin/bandara/index.php">
                <i class="fa-solid fa-map-location-dot"></i>
                <span>Kelola Bandara</span>
            </a>
        </li>
        <li class="sidebar-item <?= ($current_dir == 'penerbangan' && $current_page == 'index.php') ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>admin/penerbangan/index.php">
                <i class="fa-solid fa-route"></i>
                <span>Kelola Penerbangan</span>
            </a>
        </li>
        <li class="sidebar-item <?= ($current_dir == 'penerbangan' && $current_page == 'barat.php') ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>admin/penerbangan/barat.php">
                <i class="fa-solid fa-compass text-warning"></i>
                <span>Penerbangan Barat</span>
            </a>
        </li>
        <li class="sidebar-item <?= ($current_dir == 'penerbangan' && $current_page == 'timur.php') ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>admin/penerbangan/timur.php">
                <i class="fa-solid fa-compass text-info"></i>
                <span>Penerbangan Timur</span>
            </a>
        </li>
        <li class="sidebar-item <?= ($current_dir == 'user') ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>admin/user/index.php">
                <i class="fa-solid fa-users"></i>
                <span>Kelola User</span>
            </a>
        </li>
        <li class="sidebar-item <?= ($current_dir == 'laporan') ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>admin/laporan/index.php">
                <i class="fa-solid fa-file-invoice-dollar"></i>
                <span>Laporan Database</span>
            </a>
        </li>
        <li class="sidebar-item <?= ($current_page == 'log_aktivitas.php') ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>admin/log_aktivitas.php">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <span>Log Aktivitas</span>
            </a>
        </li>
        <li class="sidebar-item <?= ($current_page == 'simulasi_deadlock.php') ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>admin/simulasi_deadlock.php">
                <i class="fa-solid fa-bug text-danger"></i>
                <span>Simulasi Deadlock</span>
            </a>
        </li>
        <li class="sidebar-item <?= ($current_dir == 'backup') ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>admin/backup/index.php">
                <i class="fa-solid fa-database"></i>
                <span>Backup Database</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-footer">
        <div class="d-flex align-items-center justify-content-between">
            <span class="text-white-50" style="font-size: 0.85rem;">Admin Panel</span>
            <a href="<?= BASE_URL ?>auth/logout.php" class="text-danger" title="Logout">
                <i class="fa-solid fa-right-from-bracket fs-5"></i>
            </a>
        </div>
    </div>
</aside>
