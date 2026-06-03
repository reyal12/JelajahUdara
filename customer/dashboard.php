<?php
$page_title = "Dashboard Pelanggan";
require_once '../includes/header.php';
require_once '../includes/navbar.php';
require_once '../config/database.php';

// Authentication Check
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Silakan login terlebih dahulu!";
    header("Location: ../auth/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

// Get statistics
$active_tickets = 0;
$total_trips = 0;
$pending_payments = 0;
$latest_ticket = null;

try {
    // 1. Active tickets (status dikonfirmasi)
    $q_active = "SELECT COUNT(*) AS total FROM pemesanan WHERE id_user = :user_id AND status_pemesanan = 'dikonfirmasi'";
    $s_active = $db->prepare($q_active);
    $s_active->execute([':user_id' => $user_id]);
    $active_tickets = $s_active->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // 2. Total trips (any status)
    $q_trips = "SELECT COUNT(*) AS total FROM pemesanan WHERE id_user = :user_id";
    $s_trips = $db->prepare($q_trips);
    $s_trips->execute([':user_id' => $user_id]);
    $total_trips = $s_trips->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // 3. Pending payments
    $q_pending = "SELECT COUNT(*) AS total FROM pemesanan WHERE id_user = :user_id AND status_pemesanan = 'pending'";
    $s_pending = $db->prepare($q_pending);
    $s_pending->execute([':user_id' => $user_id]);
    $pending_payments = $s_pending->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // 4. Latest ticket
    $q_latest = "SELECT p.*, CONCAT(f.tanggal_berangkat, ' ', f.jam_berangkat) AS tanggal_berangkat, m.nama_maskapai, 
                        b_asal.kota AS kota_asal, b_asal.kode_bandara AS kode_asal,
                        b_tuj.kota AS kota_tujuan, b_tuj.kode_bandara AS kode_tujuan
                 FROM pemesanan p
                 JOIN penerbangan f ON p.id_penerbangan = f.id_penerbangan
                 JOIN maskapai m ON f.id_maskapai = m.id_maskapai
                 JOIN bandara b_asal ON f.asal_bandara = b_asal.id_bandara
                 JOIN bandara b_tuj ON f.tujuan_bandara = b_tuj.id_bandara
                 WHERE p.id_user = :user_id
                 ORDER BY p.tanggal_pesan DESC LIMIT 1";
    $s_latest = $db->prepare($q_latest);
    $s_latest->execute([':user_id' => $user_id]);
    $latest_ticket = $s_latest->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {}
?>

<main class="container my-5">
    <div class="row">
        <!-- Customer navigation menu -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="text-center py-3 border-bottom mb-3">
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle mx-auto mb-3" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-user fs-2"></i>
                    </div>
                    <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($_SESSION['user_name']) ?></h6>
                    <small class="text-muted"><?= htmlspecialchars($_SESSION['user_email']) ?></small>
                </div>
                <div class="list-group list-group-flush">
                    <a href="dashboard.php" class="list-group-item list-group-item-action border-0 py-2.5 rounded-3 active"><i class="fa-solid fa-gauge me-3"></i> Dashboard</a>
                    <a href="cari_tiket.php" class="list-group-item list-group-item-action border-0 py-2.5 rounded-3"><i class="fa-solid fa-plane-departure me-3 text-secondary"></i> Cari Tiket</a>
                    <a href="riwayat.php" class="list-group-item list-group-item-action border-0 py-2.5 rounded-3"><i class="fa-solid fa-receipt me-3 text-secondary"></i> Pemesanan Saya</a>
                    <a href="profil.php" class="list-group-item list-group-item-action border-0 py-2.5 rounded-3"><i class="fa-solid fa-user-gear me-3 text-secondary"></i> Profil Saya</a>
                    <a href="../auth/logout.php" class="list-group-item list-group-item-action border-0 py-2.5 rounded-3 text-danger"><i class="fa-solid fa-right-from-bracket me-3"></i> Logout</a>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="col-lg-9">
            <h4 class="fw-bold mb-4 text-secondary">Selamat Datang, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h4>

            <!-- Stats cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white d-flex flex-row align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold">Tiket Aktif</span>
                            <h3 class="fw-bold text-primary mb-0 mt-1"><?= $active_tickets ?></h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3">
                            <i class="fa-solid fa-ticket fs-3"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white d-flex flex-row align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold">Total Perjalanan</span>
                            <h3 class="fw-bold text-success mb-0 mt-1"><?= $total_trips ?></h3>
                        </div>
                        <div class="bg-success bg-opacity-10 text-success p-3 rounded-3">
                            <i class="fa-solid fa-plane-departure fs-3"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white d-flex flex-row align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold">Butuh Pembayaran</span>
                            <h3 class="fw-bold text-warning mb-0 mt-1"><?= $pending_payments ?></h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-3">
                            <i class="fa-solid fa-credit-card fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Latest trip card -->
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                <h5 class="fw-bold mb-3 text-secondary border-bottom pb-2">Aktivitas Pemesanan Terbaru</h5>
                
                <?php if ($latest_ticket): ?>
                    <div class="p-3 bg-light rounded-4 border d-flex flex-column flex-md-row justify-content-between align-items-md-center g-3">
                        <div>
                            <span class="badge bg-secondary-subtle text-secondary mb-2"><?= htmlspecialchars($latest_ticket['kode_booking']) ?></span>
                            <h6 class="fw-bold mb-1"><?= htmlspecialchars($latest_ticket['nama_maskapai']) ?></h6>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <strong class="text-dark"><?= htmlspecialchars($latest_ticket['kota_asal']) ?> (<?= $latest_ticket['kode_asal'] ?>)</strong>
                                <i class="fa-solid fa-arrow-right text-muted"></i>
                                <strong class="text-dark"><?= htmlspecialchars($latest_ticket['kota_tujuan']) ?> (<?= $latest_ticket['kode_tujuan'] ?>)</strong>
                            </div>
                            <small class="text-muted d-block mt-2"><i class="fa-solid fa-calendar me-1"></i> Keberangkatan: <?= date('d M Y, H:i', strtotime($latest_ticket['tanggal_berangkat'])) ?></small>
                        </div>
                        <div class="text-md-end">
                            <h5 class="fw-bold text-success mb-2">Rp <?= number_format($latest_ticket['total_harga'], 0, ',', '.') ?></h5>
                            <div>
                                <?php 
                                if ($latest_ticket['status_pemesanan'] == 'pending') {
                                    echo '<a href="pembayaran.php?id=' . $latest_ticket['id_pemesanan'] . '" class="btn btn-warning btn-sm fw-bold px-3"><i class="fa-solid fa-credit-card me-1"></i> Bayar Sekarang</a>';
                                } elseif ($latest_ticket['status_pemesanan'] == 'dikonfirmasi') {
                                    echo '<a href="cetak_tiket.php?id=' . $latest_ticket['id_pemesanan'] . '" target="_blank" class="btn btn-outline-primary btn-sm px-3"><i class="fa-solid fa-print me-1"></i> Cetak E-Ticket</a>';
                                } else {
                                    echo '<span class="badge bg-danger">Gagal</span>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fa-solid fa-receipt text-muted fs-1 mb-2"></i>
                        <p class="text-muted mb-0">Belum ada pemesanan tiket pesawat baru-baru ini.</p>
                        <a href="cari_tiket.php" class="btn btn-primary btn-sm px-3 mt-2"><i class="fa-solid fa-magnifying-glass me-1"></i> Cari Tiket Sekarang</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
