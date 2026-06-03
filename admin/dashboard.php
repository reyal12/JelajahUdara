<?php
$page_title = "Dashboard Admin";
require_once '../includes/header.php';

// Authentication & Role Check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error'] = "Akses ditolak! Anda harus masuk sebagai Admin.";
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Aggregate stats using SQL functions
$total_users = 0;
$total_maskapai = 0;
$total_bandara = 0;
$total_penerbangan = 0;
$total_tiket = 0;
$total_pendapatan = 0;
$avg_harga_penerbangan = 0;

try {
    // 1. COUNT Users
    $s_users = $db->query("SELECT COUNT(id_user) AS total FROM users");
    $total_users = $s_users->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // 2. COUNT Maskapai
    $s_maskapai = $db->query("SELECT COUNT(id_maskapai) AS total FROM maskapai");
    $total_maskapai = $s_maskapai->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // 3. COUNT Bandara
    $s_bandara = $db->query("SELECT COUNT(id_bandara) AS total FROM bandara");
    $total_bandara = $s_bandara->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // 4. COUNT Penerbangan
    $s_penerbangan = $db->query("SELECT COUNT(id_penerbangan) AS total FROM penerbangan");
    $total_penerbangan = $s_penerbangan->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // 5. COUNT Tiket & SUM Revenue
    $s_sales = $db->query("SELECT COUNT(id_pemesanan) AS total_t, SUM(total_harga) AS total_rev FROM pemesanan WHERE status_pemesanan = 'Berhasil'");
    $sales_data = $s_sales->fetch(PDO::FETCH_ASSOC);
    $total_tiket = $sales_data['total_t'] ?? 0;
    $total_pendapatan = $sales_data['total_rev'] ?? 0;

    // 6. AVG Ticket Price
    $s_avg = $db->query("SELECT AVG(harga) AS avg_h FROM penerbangan");
    $avg_harga_penerbangan = $s_avg->fetch(PDO::FETCH_ASSOC)['avg_h'] ?? 0;

    // Fetch latest bookings for table dashboard listing
    $q_latest_bookings = "SELECT p.*, u.nama_lengkap, CONCAT(f.bandara_asal, ' -> ', f.bandara_tujuan) AS rute 
                          FROM pemesanan p 
                          JOIN users u ON p.id_user = u.id_user 
                          JOIN penerbangan f ON p.id_penerbangan = f.id_penerbangan
                          ORDER BY p.tanggal_pemesanan DESC LIMIT 5";
    $s_latest_bookings = $db->query($q_latest_bookings);
    $latest_bookings = $s_latest_bookings->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {}
?>

<div class="admin-wrapper">
    <!-- Include Sidebar -->
    <?php require_once '../includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <main class="admin-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Dashboard Administrator</h3>
                <p class="text-muted mb-0">Statistik real-time dan monitoring sistem JelajahUdara.</p>
            </div>
            <div class="text-end">
                <span class="badge bg-primary px-3 py-2"><i class="fa-solid fa-clock me-1"></i> <?= date('d M Y') ?></span>
            </div>
        </div>

        <!-- Metric Stat Cards -->
        <div class="row g-3 mb-4">
            <!-- Total User -->
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white h-100 d-flex flex-row align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">Total User</span>
                        <h3 class="fw-bold text-primary mb-0 mt-1"><?= $total_users ?></h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3 d-none d-sm-block">
                        <i class="fa-solid fa-users fs-3"></i>
                    </div>
                </div>
            </div>

            <!-- Total Maskapai -->
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white h-100 d-flex flex-row align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">Total Maskapai</span>
                        <h3 class="fw-bold text-success mb-0 mt-1"><?= $total_maskapai ?></h3>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success p-3 rounded-3 d-none d-sm-block">
                        <i class="fa-solid fa-plane fs-3"></i>
                    </div>
                </div>
            </div>

            <!-- Total Bandara -->
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white h-100 d-flex flex-row align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">Total Bandara</span>
                        <h3 class="fw-bold text-info mb-0 mt-1"><?= $total_bandara ?></h3>
                    </div>
                    <div class="bg-info bg-opacity-10 text-info p-3 rounded-3 d-none d-sm-block">
                        <i class="fa-solid fa-map-location-dot fs-3"></i>
                    </div>
                </div>
            </div>

            <!-- Total Penerbangan -->
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white h-100 d-flex flex-row align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">Penerbangan</span>
                        <h3 class="fw-bold text-warning mb-0 mt-1"><?= $total_penerbangan ?></h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-3 d-none d-sm-block">
                        <i class="fa-solid fa-route fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <!-- Total Tiket Terjual -->
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 d-flex flex-row align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">Tiket Terjual</span>
                        <h3 class="fw-bold text-danger mb-0 mt-1"><?= $total_tiket ?></h3>
                    </div>
                    <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-3">
                        <i class="fa-solid fa-ticket-simple fs-3"></i>
                    </div>
                </div>
            </div>

            <!-- Total Pendapatan -->
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 d-flex flex-row align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">Total Pendapatan</span>
                        <h3 class="fw-bold text-success mb-0 mt-1">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></h3>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success p-3 rounded-3">
                        <i class="fa-solid fa-rupiah-sign fs-3"></i>
                    </div>
                </div>
            </div>

            <!-- Average price -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 d-flex flex-row align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">Rata-rata Harga Tiket</span>
                        <h3 class="fw-bold text-secondary mb-0 mt-1">Rp <?= number_format($avg_harga_penerbangan, 0, ',', '.') ?></h3>
                    </div>
                    <div class="bg-secondary bg-opacity-10 text-secondary p-3 rounded-3">
                        <i class="fa-solid fa-calculator fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Latest Bookings Table -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
            <h5 class="fw-bold text-secondary mb-3 border-bottom pb-2">Transaksi Terbaru</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Kode Booking</th>
                            <th>Nama Customer</th>
                            <th>Jumlah Pax</th>
                            <th>Total Harga</th>
                            <th>Status</th>
                            <th>Tanggal Pemesanan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($latest_bookings)): ?>
                            <?php foreach ($latest_bookings as $b): ?>
                                <tr>
                                    <td class="font-monospace fw-bold"><?= htmlspecialchars($b['kode_booking']) ?></td>
                                    <td><?= htmlspecialchars($b['nama_lengkap']) ?></td>
                                    <td><?= $b['jumlah_penumpang'] ?></td>
                                    <td class="fw-bold text-success">Rp <?= number_format($b['total_harga'], 0, ',', '.') ?></td>
                                    <td>
                                        <?php 
                                        if ($b['status_pemesanan'] == 'Pending') {
                                            echo '<span class="badge badge-pending px-2 py-1 rounded-pill">Pending</span>';
                                        } elseif ($b['status_pemesanan'] == 'Berhasil') {
                                            echo '<span class="badge badge-success px-2 py-1 rounded-pill">Berhasil</span>';
                                        } else {
                                            echo '<span class="badge badge-danger px-2 py-1 rounded-pill">Gagal</span>';
                                        }
                                        ?>
                                    </td>
                                    <td><?= date('d M Y, H:i', strtotime($b['tanggal_pemesanan'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Belum ada transaksi pemesanan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php 
$is_admin_layout = true;
require_once '../includes/footer.php'; 
?>
