<?php
$page_title = "Riwayat Pemesanan Tiket";
require_once '../includes/header.php';
require_once '../includes/navbar.php';
require_once '../config/database.php';

// Authentication Check
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Silakan login terlebih dahulu untuk melihat riwayat!";
    header("Location: ../auth/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$bookings = [];
try {
    $query = "SELECT p.*, f.tanggal_berangkat, f.jam_berangkat, m.nama_maskapai, m.kode_maskapai,
                     b_asal.kota AS kota_asal, b_asal.kode_bandara AS kode_asal,
                     b_tuj.kota AS kota_tujuan, b_tuj.kode_bandara AS kode_tujuan
              FROM pemesanan p
              JOIN penerbangan f ON p.id_penerbangan = f.id_penerbangan
              JOIN maskapai m ON f.id_maskapai = m.id_maskapai
              JOIN bandara b_asal ON f.asal_bandara = b_asal.id_bandara
              JOIN bandara b_tuj ON f.tujuan_bandara = b_tuj.id_bandara
              WHERE p.id_user = :user_id 
              ORDER BY p.tanggal_pesan DESC";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                    <a href="dashboard.php" class="list-group-item list-group-item-action border-0 py-2.5 rounded-3"><i class="fa-solid fa-gauge me-3 text-secondary"></i> Dashboard</a>
                    <a href="cari_tiket.php" class="list-group-item list-group-item-action border-0 py-2.5 rounded-3"><i class="fa-solid fa-plane-departure me-3 text-secondary"></i> Cari Tiket</a>
                    <a href="riwayat.php" class="list-group-item list-group-item-action border-0 py-2.5 rounded-3 active"><i class="fa-solid fa-receipt me-3"></i> Pemesanan Saya</a>
                    <a href="profil.php" class="list-group-item list-group-item-action border-0 py-2.5 rounded-3"><i class="fa-solid fa-user-gear me-3 text-secondary"></i> Profil Saya</a>
                    <a href="../auth/logout.php" class="list-group-item list-group-item-action border-0 py-2.5 rounded-3 text-danger"><i class="fa-solid fa-right-from-bracket me-3"></i> Logout</a>
                </div>
            </div>
        </div>

        <!-- History Content -->
        <div class="col-lg-9">
            <h4 class="fw-bold mb-4 text-secondary">Riwayat Pemesanan Saya</h4>

            <?php if (isset($_SESSION['success_pembayaran'])): ?>
                <div class="alert alert-success alert-dismissible fade show p-3 rounded-3 mb-4 shadow-sm" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i> <?= $_SESSION['success_pembayaran']; unset($_SESSION['success_pembayaran']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="d-flex flex-column gap-3">
                <?php if (!empty($bookings)): ?>
                    <?php foreach ($bookings as $b): ?>
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                            <div class="card-header bg-light border-bottom-0 py-3 px-4 d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted small">Kode Booking: </span>
                                    <span class="font-monospace fw-bold text-dark"><?= htmlspecialchars($b['kode_booking']) ?></span>
                                </div>
                                <div>
                                    <?php 
                                    if ($b['status_pemesanan'] == 'pending') {
                                        echo '<span class="badge badge-pending px-3 py-1.5 rounded-pill"><i class="fa-solid fa-spinner fa-spin me-1"></i> Pending</span>';
                                    } elseif ($b['status_pemesanan'] == 'dikonfirmasi') {
                                        echo '<span class="badge badge-success px-3 py-1.5 rounded-pill"><i class="fa-solid fa-circle-check me-1"></i> Berhasil</span>';
                                    } else {
                                        echo '<span class="badge badge-danger px-3 py-1.5 rounded-pill"><i class="fa-solid fa-circle-xmark me-1"></i> Gagal</span>';
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <div class="card-body p-4">
                                <div class="row align-items-center g-3">
                                    <div class="col-md-3">
                                        <h6 class="fw-bold mb-0 text-primary"><?= htmlspecialchars($b['nama_maskapai']) ?></h6>
                                        <small class="text-muted"><?= htmlspecialchars($b['kode_maskapai']) ?></small>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="d-flex align-items-center justify-content-between justify-content-md-around text-center">
                                            <div>
                                                <strong class="text-dark d-block"><?= htmlspecialchars($b['kode_asal']) ?></strong>
                                                <small class="text-muted small"><?= htmlspecialchars($b['kota_asal']) ?></small>
                                            </div>
                                            <i class="fa-solid fa-arrow-right text-muted mx-2"></i>
                                            <div>
                                                <strong class="text-dark d-block"><?= htmlspecialchars($b['kode_tujuan']) ?></strong>
                                                <small class="text-muted small"><?= htmlspecialchars($b['kota_tujuan']) ?></small>
                                            </div>
                                        </div>
                                        <div class="text-center mt-2">
                                            <small class="text-muted"><i class="fa-solid fa-calendar me-1"></i> <?= date('d M Y', strtotime($b['tanggal_berangkat'])) . ', ' . substr($b['jam_berangkat'], 0, 5) ?></small>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-md-end text-center">
                                        <div class="mb-3 mb-md-0">
                                            <small class="text-muted d-block"><?= $b['jumlah_tiket'] ?> Penumpang</small>
                                            <strong class="text-success fs-5">Rp <?= number_format($b['total_harga'], 0, ',', '.') ?></strong>
                                        </div>
                                        <div class="d-flex gap-2 justify-content-center justify-content-md-end mt-2">
                                            <?php if ($b['status_pemesanan'] === 'pending'): ?>
                                                <a href="pembayaran.php?id=<?= $b['id_pemesanan'] ?>" class="btn btn-primary btn-sm px-3">
                                                    <i class="fa-solid fa-credit-card me-1"></i> Bayar Sekarang
                                                </a>
                                            <?php elseif ($b['status_pemesanan'] === 'dikonfirmasi'): ?>
                                                <a href="cetak_tiket.php?id=<?= $b['id_pemesanan'] ?>" target="_blank" class="btn btn-outline-primary btn-sm px-3">
                                                    <i class="fa-solid fa-print me-1"></i> Cetak E-Ticket
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="card border-0 shadow-sm p-5 text-center bg-white rounded-4">
                        <i class="fa-solid fa-receipt text-muted display-2 mb-4"></i>
                        <h5 class="fw-bold">Belum Ada Pemesanan</h5>
                        <p class="text-muted mb-0">Anda belum pernah melakukan pemesanan tiket pesawat.</p>
                        <a href="cari_tiket.php" class="btn btn-primary px-4 py-2 mt-3 align-self-center"><i class="fa-solid fa-plane-departure me-2"></i> Mulai Cari Tiket</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
