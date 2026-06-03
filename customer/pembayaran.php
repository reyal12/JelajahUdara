<?php
$page_title = "Pembayaran Tiket";
require_once '../includes/header.php';
require_once '../includes/navbar.php';
require_once '../config/database.php';

// Authentication Check
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Silakan login terlebih dahulu!";
    header("Location: ../auth/login.php");
    exit();
}

$id_pemesanan = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_pemesanan <= 0 && !isset($_POST['id_pemesanan'])) {
    header("Location: dashboard.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Handle Payment Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proses_bayar'])) {
    $id_pemesanan = intval($_POST['id_pemesanan']);
    $metode_pembayaran = filter_input(INPUT_POST, 'metode_pembayaran', FILTER_SANITIZE_SPECIAL_CHARS);
    $jumlah_bayar = floatval($_POST['jumlah_bayar']);
    $kode_booking = filter_input(INPUT_POST, 'kode_booking', FILTER_SANITIZE_SPECIAL_CHARS);
    
    // In real system, check uploaded file. For native CRUD, we'll auto-approve or mock success
    $status_pembayaran = 'Berhasil'; // Automatically set to Berhasil for instant demo flow

    try {
        $db->beginTransaction();

        // 1. Insert into pembayaran table
        $query_pay = "INSERT INTO pembayaran (id_pemesanan, kode_booking, metode_pembayaran, jumlah_bayar, status_pembayaran) 
                      VALUES (:id_pemesanan, :kode_booking, :metode_pembayaran, :jumlah_bayar, :status_pembayaran)";
        $stmt_pay = $db->prepare($query_pay);
        $stmt_pay->bindParam(':id_pemesanan', $id_pemesanan);
        $stmt_pay->bindParam(':kode_booking', $kode_booking);
        $stmt_pay->bindParam(':metode_pembayaran', $metode_pembayaran);
        $stmt_pay->bindParam(':jumlah_bayar', $jumlah_bayar);
        $stmt_pay->bindParam(':status_pembayaran', $status_pembayaran);
        $stmt_pay->execute();

        // 2. Update status_pemesanan in pemesanan table to 'Berhasil'
        $query_book = "UPDATE pemesanan SET status_pemesanan = 'Berhasil' WHERE id_pemesanan = :id";
        $stmt_book = $db->prepare($query_book);
        $stmt_book->bindParam(':id', $id_pemesanan);
        $stmt_book->execute();

        $db->commit();
        $_SESSION['success_pembayaran'] = "Pembayaran berhasil dikonfirmasi! E-Ticket Anda kini aktif.";
        header("Location: riwayat.php");
        exit();

    } catch (Exception $e) {
        $db->rollBack();
        $payment_error = "Terjadi kesalahan pembayaran: " . $e->getMessage();
    }
}

// Fetch Booking details
$booking = null;
try {
    $query = "SELECT p.*, f.tanggal_berangkat, f.tanggal_tiba, m.nama_maskapai, 
                     b_asal.kota AS kota_asal, b_asal.kode_bandara AS kode_asal,
                     b_tuj.kota AS kota_tujuan, b_tuj.kode_bandara AS kode_tujuan
              FROM pemesanan p
              JOIN penerbangan f ON p.id_penerbangan = f.id_penerbangan
              JOIN maskapai m ON f.id_maskapai = m.id_maskapai
              JOIN bandara b_asal ON f.bandara_asal = b_asal.id_bandara
              JOIN bandara b_tuj ON f.bandara_tujuan = b_tuj.id_bandara
              WHERE p.id_pemesanan = :id AND p.id_user = :user_id LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id_pemesanan);
    $stmt->bindValue(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

if (!$booking) {
    echo "<div class='container my-5 text-center'><div class='alert alert-danger'>Pemesanan tidak ditemukan atau Anda tidak memiliki akses!</div><a href='dashboard.php' class='btn btn-primary'>Kembali ke Dashboard</a></div>";
    require_once '../includes/footer.php';
    exit();
}

if ($booking['status_pemesanan'] === 'Berhasil') {
    $_SESSION['success'] = "Pemesanan ini sudah lunas!";
    header("Location: riwayat.php");
    exit();
}
?>

<main class="container my-5">
    <div class="row justify-content-center g-4">
        
        <?php if (isset($_SESSION['booking_success'])): ?>
            <div class="col-lg-10">
                <div class="alert alert-success alert-dismissible fade show p-4 rounded-4 shadow-sm" role="alert">
                    <h5 class="fw-bold mb-1"><i class="fa-solid fa-circle-check me-2"></i> Transaksi Berhasil!</h5>
                    <p class="mb-0"><?= $_SESSION['booking_msg']; unset($_SESSION['booking_success']); unset($_SESSION['booking_msg']); ?></p>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($payment_error)): ?>
            <div class="col-lg-10">
                <div class="alert alert-danger p-4 rounded-4 shadow-sm" role="alert">
                    <h5 class="fw-bold mb-1"><i class="fa-solid fa-circle-xmark me-2"></i> Transaksi Gagal</h5>
                    <p class="mb-0"><?= $payment_error ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Payment Details Form -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <h4 class="fw-bold mb-4 text-secondary"><i class="fa-solid fa-credit-card text-primary me-2"></i> Metode Pembayaran</h4>
                
                <form action="" method="POST">
                    <input type="hidden" name="id_pemesanan" value="<?= $booking['id_pemesanan'] ?>">
                    <input type="hidden" name="kode_booking" value="<?= $booking['kode_booking'] ?>">
                    <input type="hidden" name="jumlah_bayar" value="<?= $booking['total_harga'] ?>">
                    
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Pilih Metode Pembayaran</label>
                        
                        <!-- Bank Transfer -->
                        <div class="form-check p-3 border rounded-3 mb-3 d-flex align-items-center">
                            <input class="form-check-input ms-0 me-3" type="radio" name="metode_pembayaran" id="pay_bank" value="Transfer Bank" checked>
                            <label class="form-check-label w-100" for="pay_bank">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong class="d-block">Transfer Bank</strong>
                                        <small class="text-muted">Transfer manual ke Bank Mandiri / BCA</small>
                                    </div>
                                    <i class="fa-solid fa-building-columns fs-3 text-secondary"></i>
                                </div>
                            </label>
                        </div>

                        <!-- E-Wallet -->
                        <div class="form-check p-3 border rounded-3 mb-3 d-flex align-items-center">
                            <input class="form-check-input ms-0 me-3" type="radio" name="metode_pembayaran" id="pay_wallet" value="E-Wallet">
                            <label class="form-check-label w-100" for="pay_wallet">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong class="d-block">E-Wallet (OVO / GoPay)</strong>
                                        <small class="text-muted">Bayar menggunakan dompet digital Anda</small>
                                    </div>
                                    <i class="fa-solid fa-wallet fs-3 text-secondary"></i>
                                </div>
                            </label>
                        </div>

                        <!-- Virtual Account -->
                        <div class="form-check p-3 border rounded-3 mb-3 d-flex align-items-center">
                            <input class="form-check-input ms-0 me-3" type="radio" name="metode_pembayaran" id="pay_va" value="Virtual Account">
                            <label class="form-check-label w-100" for="pay_va">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong class="d-block">Virtual Account (VA)</strong>
                                        <small class="text-muted">Pembayaran instan otomatis terkonfirmasi</small>
                                    </div>
                                    <i class="fa-solid fa-qrcode fs-3 text-secondary"></i>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Payment details explanation box -->
                    <div id="payment_instructions" class="p-3 bg-light rounded-3 border mb-4">
                        <h6 class="fw-bold mb-2">Instruksi Transfer:</h6>
                        <p class="small text-muted mb-1">Kirim pembayaran sebesar nominal tagihan ke:</p>
                        <div class="d-flex align-items-center justify-content-between bg-white p-2 border rounded mb-2">
                            <span class="font-monospace fw-bold text-primary">Mandiri 123-000-4567-890</span>
                            <small class="text-muted">a.n JelajahUdara</small>
                        </div>
                        <p class="small text-muted mb-0">Pembayaran akan diverifikasi secara otomatis demi kemudahan Anda.</p>
                    </div>

                    <button type="submit" name="proses_bayar" class="btn btn-primary btn-lg w-100 py-3 fw-bold mt-2">
                        <i class="fa-solid fa-circle-check me-2"></i> Konfirmasi Bayar Sekarang
                    </button>
                </form>
            </div>
        </div>

        <!-- Summary Column -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <h5 class="fw-bold mb-4 text-secondary"><i class="fa-solid fa-receipt text-primary me-2"></i> Ringkasan Tagihan</h5>
                
                <div class="mb-3">
                    <span class="text-muted small d-block">Kode Booking:</span>
                    <h5 class="font-monospace fw-bold text-dark mb-0"><?= htmlspecialchars($booking['kode_booking']) ?></h5>
                </div>
                
                <hr>

                <div class="py-2">
                    <h6 class="fw-bold mb-1 text-secondary"><?= htmlspecialchars($booking['nama_maskapai']) ?></h6>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <strong><?= htmlspecialchars($booking['kota_asal']) ?></strong>
                        <i class="fa-solid fa-arrow-right text-muted mx-2"></i>
                        <strong><?= htmlspecialchars($booking['kota_tujuan']) ?></strong>
                    </div>
                    <small class="text-muted d-block mt-1"><?= date('d M Y, H:i', strtotime($booking['tanggal_berangkat'])) ?></small>
                </div>

                <hr>

                <div class="d-flex justify-content-between align-items-center my-3 bg-light p-3 rounded-3">
                    <div>
                        <span class="small text-muted d-block">Jumlah Tagihan</span>
                        <h4 class="fw-bold text-success mb-0">Rp <?= number_format($booking['total_harga'], 0, ',', '.') ?></h4>
                    </div>
                    <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill fw-bold">Pending</span>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    // Dynamically change instructions based on payment method
    const instructions = document.getElementById('payment_instructions');
    const payBank = document.getElementById('pay_bank');
    const payWallet = document.getElementById('pay_wallet');
    const payVa = document.getElementById('pay_va');

    function updateInstructions() {
        if (payBank.checked) {
            instructions.innerHTML = `
                <h6 class="fw-bold mb-2">Instruksi Transfer Bank:</h6>
                <p class="small text-muted mb-1">Transfer tepat hingga digit terakhir ke rekening kami:</p>
                <div class="d-flex align-items-center justify-content-between bg-white p-2 border rounded mb-2">
                    <span class="font-monospace fw-bold text-primary">MANDIRI: 123-000-4567-890</span>
                    <small class="text-muted">a.n JelajahUdara</small>
                </div>
                <div class="d-flex align-items-center justify-content-between bg-white p-2 border rounded mb-2">
                    <span class="font-monospace fw-bold text-primary">BCA: 888-099-2345</span>
                    <small class="text-muted">a.n JelajahUdara</small>
                </div>
            `;
        } else if (payWallet.checked) {
            instructions.innerHTML = `
                <h6 class="fw-bold mb-2">Instruksi E-Wallet:</h6>
                <p class="small text-muted mb-1">Lakukan scan QR Code atau bayar menggunakan nomor HP terdaftar:</p>
                <div class="d-flex align-items-center justify-content-between bg-white p-2 border rounded mb-2">
                    <span class="font-monospace fw-bold text-primary">OVO / GoPay: 0812-3456-7890</span>
                    <small class="text-muted">a.n JelajahUdara</small>
                </div>
            `;
        } else if (payVa.checked) {
            instructions.innerHTML = `
                <h6 class="fw-bold mb-2">Instruksi Virtual Account:</h6>
                <p class="small text-muted mb-1">Transfer secara instan lewat m-Banking/ATM ke nomor Virtual Account:</p>
                <div class="d-flex align-items-center justify-content-between bg-white p-2 border rounded mb-2">
                    <span class="font-monospace fw-bold text-primary">VA MANDIRI: 88909-081234567890</span>
                    <small class="text-muted">Otomatis Terverifikasi</small>
                </div>
            `;
        }
    }

    payBank.addEventListener('change', updateInstructions);
    payWallet.addEventListener('change', updateInstructions);
    payVa.addEventListener('change', updateInstructions);
    updateInstructions();
</script>

<?php require_once '../includes/footer.php'; ?>
