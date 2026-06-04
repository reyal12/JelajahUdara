<?php
$page_title = "Pembayaran Tiket";
require_once '../includes/header.php';
require_once '../includes/navbar.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Silakan login terlebih dahulu untuk melanjutkan pembayaran.";
    header("Location: ../auth/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$booking = null;
$payment_error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pemesanan = isset($_POST['id_pemesanan']) ? intval($_POST['id_pemesanan']) : 0;

    if ($id_pemesanan <= 0) {
        $_SESSION['error'] = "Pemesanan tidak valid.";
        header("Location: ../customer/riwayat.php");
        exit();
    }

    try {
        $query = "SELECT p.*, f.tanggal_berangkat, f.jam_berangkat, f.jam_tiba, m.nama_maskapai, m.kode_maskapai,
                         b_asal.kota AS kota_asal, b_asal.kode_bandara AS kode_asal,
                         b_tuj.kota AS kota_tujuan, b_tuj.kode_bandara AS kode_tujuan
                  FROM pemesanan p
                  JOIN penerbangan f ON p.id_penerbangan = f.id_penerbangan
                  JOIN maskapai m ON f.id_maskapai = m.id_maskapai
                  JOIN bandara b_asal ON f.asal_bandara = b_asal.id_bandara
                  JOIN bandara b_tuj ON f.tujuan_bandara = b_tuj.id_bandara
                  WHERE p.id_pemesanan = :id AND p.id_user = :user_id LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id_pemesanan);
        $stmt->bindValue(':user_id', $_SESSION['user_id']);
        $stmt->execute();
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            $_SESSION['error'] = "Pemesanan tidak ditemukan.";
            header("Location: ../customer/riwayat.php");
            exit();
        }

        if ($booking['status_pemesanan'] !== 'pending') {
            $_SESSION['info'] = "Pembayaran sudah diproses atau pemesanan tidak lagi menunggu pembayaran.";
            header("Location: ../customer/riwayat.php");
            exit();
        }

        $update = "UPDATE pemesanan SET status_pemesanan = 'dikonfirmasi' WHERE id_pemesanan = :id AND id_user = :user_id AND status_pemesanan = 'pending'";
        $stmt_update = $db->prepare($update);
        $stmt_update->bindParam(':id', $id_pemesanan);
        $stmt_update->bindValue(':user_id', $_SESSION['user_id']);
        $stmt_update->execute();

        if ($stmt_update->rowCount() > 0) {
            $_SESSION['success_pembayaran'] = "Pembayaran berhasil dikonfirmasi. Tiket Anda sekarang sudah aktif.";
            header("Location: ../customer/riwayat.php");
            exit();
        }

        $payment_error = "Gagal memproses pembayaran. Silakan coba lagi.";
    } catch (PDOException $e) {
        $payment_error = "Terjadi kesalahan saat memproses pembayaran: " . $e->getMessage();
    }
}

$id_pemesanan = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_pemesanan <= 0 && !$booking) {
    $_SESSION['error'] = "Pemesanan tidak valid.";
    header("Location: ../customer/riwayat.php");
    exit();
}

if (!$booking) {
    try {
        $query = "SELECT p.*, f.tanggal_berangkat, f.jam_berangkat, f.jam_tiba, m.nama_maskapai, m.kode_maskapai,
                         b_asal.kota AS kota_asal, b_asal.kode_bandara AS kode_asal,
                         b_tuj.kota AS kota_tujuan, b_tuj.kode_bandara AS kode_tujuan
                  FROM pemesanan p
                  JOIN penerbangan f ON p.id_penerbangan = f.id_penerbangan
                  JOIN maskapai m ON f.id_maskapai = m.id_maskapai
                  JOIN bandara b_asal ON f.asal_bandara = b_asal.id_bandara
                  JOIN bandara b_tuj ON f.tujuan_bandara = b_tuj.id_bandara
                  WHERE p.id_pemesanan = :id AND p.id_user = :user_id LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id_pemesanan);
        $stmt->bindValue(':user_id', $_SESSION['user_id']);
        $stmt->execute();
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $booking = null;
    }
}

if (!$booking) {
    echo "<div class='container my-5'><div class='alert alert-danger'>Pemesanan tidak ditemukan atau Anda tidak memiliki akses.</div></div>";
    require_once '../includes/footer.php';
    exit();
}
?>

<main class="container my-5">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <h4 class="fw-bold mb-4 text-secondary"><i class="fa-solid fa-credit-card text-primary me-2"></i> Pembayaran Tiket</h4>

                <?php if ($payment_error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($payment_error) ?></div>
                <?php endif; ?>

                <?php if ($booking['status_pemesanan'] === 'pending'): ?>
                    <div class="alert alert-warning">
                        <i class="fa-solid fa-circle-exclamation me-2"></i> Silakan selesaikan pembayaran untuk memproses tiket Anda.
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-sm-6">
                            <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                                <h6 class="fw-semibold mb-3">Transfer Bank</h6>
                                <p class="small text-muted mb-2">Bank Mandiri</p>
                                <p class="mb-0"><strong>123-456-7890</strong></p>
                                <p class="small text-muted">Nama: PT Jelajah Udara</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                                <h6 class="fw-semibold mb-3">E-Wallet / VA</h6>
                                <p class="small text-muted mb-2">OVO / GoPay / Dana</p>
                                <p class="mb-0"><strong>VA: 9876543210</strong></p>
                                <p class="small text-muted">Gunakan kode booking sebagai berita transfer</p>
                            </div>
                        </div>
                    </div>

                    <form action="" method="POST">
                        <input type="hidden" name="id_pemesanan" value="<?= htmlspecialchars($booking['id_pemesanan']) ?>">
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Kode Booking</label>
                            <div class="form-control bg-light"><?= htmlspecialchars($booking['kode_booking']) ?></div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Total Pembayaran</label>
                            <div class="form-control bg-light">Rp <?= number_format($booking['total_harga'], 0, ',', '.') ?></div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Metode Pembayaran</label>
                            <select class="form-select" disabled>
                                <option selected>Transfer Bank Mandiri</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success btn-lg w-100 py-3">
                            <i class="fa-solid fa-check-circle me-2"></i> Konfirmasi Pembayaran
                        </button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-success">
                        <i class="fa-solid fa-circle-check me-2"></i> Pembayaran sudah terkonfirmasi. Tiket Anda siap dicetak.
                    </div>
                    <a href="cetak_tiket.php?id=<?= htmlspecialchars($booking['id_pemesanan']) ?>" class="btn btn-primary btn-lg w-100 py-3">
                        <i class="fa-solid fa-print me-2"></i> Cetak E-Ticket
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <h5 class="fw-bold mb-3 text-secondary">Ringkasan Pesanan</h5>
                <div class="mb-3">
                    <span class="text-muted small d-block">Maskapai</span>
                    <strong class="d-block text-dark mb-2"><?= htmlspecialchars($booking['nama_maskapai']) ?> (<?= htmlspecialchars($booking['kode_maskapai']) ?>)</strong>
                    <span class="text-muted small d-block">Rute</span>
                    <strong class="d-block text-dark mb-2"><?= htmlspecialchars($booking['kota_asal']) ?> &rarr; <?= htmlspecialchars($booking['kota_tujuan']) ?></strong>
                </div>
                <div class="mb-3">
                    <span class="text-muted small d-block">Keberangkatan</span>
                    <strong class="d-block text-dark mb-2"><?= date('d M Y', strtotime($booking['tanggal_berangkat'])) ?>, <?= substr($booking['jam_berangkat'], 0, 5) ?></strong>
                    <span class="text-muted small d-block">Kedatangan</span>
                    <strong class="d-block text-dark"><?= date('d M Y', strtotime($booking['tanggal_berangkat'])) ?>, <?= substr($booking['jam_tiba'], 0, 5) ?></strong>
                </div>
                <div class="border-top pt-3 mt-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Jumlah Tiket</span>
                        <strong><?= htmlspecialchars($booking['jumlah_tiket']) ?>x</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Status Pembayaran</span>
                        <strong><?= htmlspecialchars(ucwords($booking['status_pemesanan'])) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <span class="fw-bold">Total</span>
                        <h4 class="fw-bold text-success mb-0">Rp <?= number_format($booking['total_harga'], 0, ',', '.') ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
