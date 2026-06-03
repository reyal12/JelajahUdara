<?php
$page_title = "Form Pemesanan Tiket";
require_once '../includes/header.php';
require_once '../includes/navbar.php';
require_once '../config/database.php';

// Authentication Check
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Silakan login terlebih dahulu untuk memesan tiket!";
    header("Location: ../auth/login.php");
    exit();
}

$id_penerbangan = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_penerbangan <= 0) {
    header("Location: cari_tiket.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$flight = null;
try {
    $query = "SELECT *, hitung_diskon(harga) AS harga_diskon 
              FROM vw_jadwal_penerbangan 
              WHERE id_penerbangan = :id LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id_penerbangan);
    $stmt->execute();
    $flight = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    try {
        $query = "SELECT * FROM vw_jadwal_penerbangan WHERE id_penerbangan = :id LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id_penerbangan);
        $stmt->execute();
        $flight = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($flight) {
            $flight['harga_diskon'] = $flight['harga'] * 0.90;
        }
    } catch (PDOException $ex) {}
}

if (!$flight) {
    echo "<div class='container my-5 text-center'><div class='alert alert-danger'>Penerbangan tidak ditemukan!</div><a href='cari_tiket.php' class='btn btn-primary'>Kembali ke Pencarian</a></div>";
    require_once '../includes/footer.php';
    exit();
}

if ($flight['kursi_tersedia'] <= 0) {
    echo "<div class='container my-5 text-center'><div class='alert alert-warning'>Maaf, penerbangan ini sudah penuh!</div><a href='cari_tiket.php' class='btn btn-primary'>Cari Penerbangan Lain</a></div>";
    require_once '../includes/footer.php';
    exit();
}
?>

<main class="container my-5">
    <div class="row g-4">
        <!-- Passenger details form -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <h4 class="fw-bold mb-4 text-secondary"><i class="fa-solid fa-address-card text-primary me-2"></i> Detail Penumpang</h4>
                
                <form action="<?= BASE_URL ?>process/booking_process.php" method="POST" id="bookingForm">
                    <input type="hidden" name="id_penerbangan" value="<?= $flight['id_penerbangan'] ?>">
                    <input type="hidden" name="harga_satuan" id="harga_satuan" value="<?= $flight['harga_diskon'] ?>">
                    
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Pemesan (Akun Terdaftar)</label>
                        <div class="p-3 bg-light rounded-3 border">
                            <h6 class="fw-bold mb-1"><?= htmlspecialchars($_SESSION['user_name']) ?></h6>
                            <p class="text-muted mb-0"><?= htmlspecialchars($_SESSION['user_email']) ?></p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="jumlah_penumpang" class="form-label fw-semibold">Jumlah Penumpang</label>
                        <div class="input-group" style="max-width: 200px;">
                            <button class="btn btn-outline-secondary" type="button" onclick="decrementCount()"><i class="fa-solid fa-minus"></i></button>
                            <input type="number" name="jumlah_penumpang" id="jumlah_penumpang" class="form-control text-center fw-bold" value="1" min="1" max="<?= $flight['kursi_tersedia'] ?>" readonly>
                            <button class="btn btn-outline-secondary" type="button" onclick="incrementCount()"><i class="fa-solid fa-plus"></i></button>
                        </div>
                        <small class="text-muted d-block mt-1">Maksimal pemesanan: <?= $flight['kursi_tersedia'] ?> kursi</small>
                    </div>

                    <div class="border-top pt-4 mt-4">
                        <div class="alert alert-info">
                            <i class="fa-solid fa-circle-info me-2"></i> Dengan menekan tombol "Proses Booking", Anda menyetujui syarat & ketentuan JelajahUdara.
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100 py-3 fw-bold">
                            <i class="fa-solid fa-receipt me-2"></i> Proses Booking Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Order summary sidebar -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <h5 class="fw-bold mb-4 text-secondary"><i class="fa-solid fa-plane-departure text-primary me-2"></i> Ringkasan Penerbangan</h5>
                
                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                    <i class="fa-solid fa-plane text-primary fs-3 me-3"></i>
                    <div>
                        <h6 class="fw-bold mb-0"><?= htmlspecialchars($flight['nama_maskapai']) ?></h6>
                        <small class="text-muted"><?= htmlspecialchars($flight['kode_maskapai']) ?></small>
                    </div>
                </div>

                <div class="row g-2 py-2">
                    <div class="col-5">
                        <span class="small text-muted d-block">Kota Asal</span>
                        <strong class="text-dark"><?= htmlspecialchars($flight['kota_asal']) ?> (<?= $flight['kode_bandara_asal'] ?>)</strong>
                    </div>
                    <div class="col-2 text-center align-self-center">
                        <i class="fa-solid fa-arrow-right text-muted"></i>
                    </div>
                    <div class="col-5 text-end">
                        <span class="small text-muted d-block">Kota Tujuan</span>
                        <strong class="text-dark"><?= htmlspecialchars($flight['kota_tujuan']) ?> (<?= $flight['kode_bandara_tujuan'] ?>)</strong>
                    </div>
                </div>

                <div class="py-2 mt-2 bg-light p-3 rounded-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Keberangkatan:</span>
                        <span class="small fw-semibold"><?= date('d M Y, H:i', strtotime($flight['tanggal_berangkat'])) ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Tiba:</span>
                        <span class="small fw-semibold"><?= date('d M Y, H:i', strtotime($flight['tanggal_tiba'])) ?></span>
                    </div>
                </div>

                <div class="border-top pt-4 mt-4">
                    <h6 class="fw-bold text-secondary mb-3">Detail Harga</h6>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Harga Satuan (Diskon)</span>
                        <span class="fw-semibold">Rp <?= number_format($flight['harga_diskon'], 0, ',', '.') ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Jumlah Penumpang</span>
                        <span class="fw-semibold" id="penumpang_label">1x</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-dark">Total Pembayaran</span>
                        <h4 class="fw-bold text-success mb-0" id="total_pembayaran_label">Rp <?= number_format($flight['harga_diskon'], 0, ',', '.') ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    const hargaSatuan = parseFloat(document.getElementById('harga_satuan').value);
    const inputPenumpang = document.getElementById('jumlah_penumpang');
    const labelPenumpang = document.getElementById('penumpang_label');
    const labelTotal = document.getElementById('total_pembayaran_label');
    const maxKursi = parseInt(inputPenumpang.getAttribute('max'));

    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(number).replace('Rp', 'Rp ');
    }

    function updateSummary() {
        const count = parseInt(inputPenumpang.value);
        labelPenumpang.innerText = count + 'x';
        const total = count * hargaSatuan;
        labelTotal.innerText = formatRupiah(total);
    }

    function incrementCount() {
        let val = parseInt(inputPenumpang.value);
        if (val < maxKursi) {
            inputPenumpang.value = val + 1;
            updateSummary();
        }
    }

    function decrementCount() {
        let val = parseInt(inputPenumpang.value);
        if (val > 1) {
            inputPenumpang.value = val - 1;
            updateSummary();
        }
    }
</script>

<?php require_once '../includes/footer.php'; ?>
