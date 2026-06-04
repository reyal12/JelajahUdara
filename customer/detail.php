<?php
$page_title = "Detail Penerbangan";
require_once '../includes/header.php';
require_once '../includes/navbar.php';
require_once '../config/database.php';

$id_penerbangan = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_penerbangan <= 0) {
    header("Location: cari_tiket.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$flight = null;
try {
    // Try to get flight detail using hitung_diskon
    $query = "SELECT *, hitung_diskon(harga) AS harga_diskon 
              FROM vw_jadwal_penerbangan 
              WHERE id_penerbangan = :id LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id_penerbangan);
    $stmt->execute();
    $flight = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Fallback if hitung_diskon does not exist
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
?>

<main class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-gradient-primary text-white py-4 border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-light text-dark px-3 py-1 rounded-pill mb-2"><i class="fa-solid fa-plane text-primary"></i> <?= htmlspecialchars($flight['nama_maskapai']) ?></span>
                            <h3 class="fw-bold mb-0"><?= htmlspecialchars($flight['kota_asal']) ?> ke <?= htmlspecialchars($flight['kota_tujuan']) ?></h3>
                        </div>
                        <div class="text-end">
                            <span class="text-white-50 small">Kode Penerbangan</span>
                            <h4 class="fw-bold mb-0">#<?= htmlspecialchars($flight['kode_maskapai']) ?>-<?= $flight['id_penerbangan'] ?></h4>
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-4 p-md-5">
                    <div class="row mb-5 g-4 align-items-center">
                        <div class="col-md-5 text-center text-md-start">
                            <span class="text-muted small d-block mb-1">BANDARA KEBERANGKATAN</span>
                            <h4 class="fw-bold text-primary mb-1"><?= htmlspecialchars($flight['kode_bandara_asal']) ?></h4>
                            <h6 class="fw-semibold text-secondary mb-2"><?= htmlspecialchars($flight['nama_bandara_asal']) ?></h6>
                            <p class="text-muted mb-0"><i class="fa-solid fa-calendar me-2"></i><?= date('d M Y', strtotime($flight['tanggal_berangkat'])) ?></p>
                            <p class="text-muted mb-0"><i class="fa-solid fa-clock me-2"></i>Jam <?= substr($flight['jam_berangkat'], 0, 5) ?> WIB</p>
                        </div>
                        
                        <div class="col-md-2 text-center my-3 my-md-0">
                            <div class="d-flex flex-column align-items-center">
                                <i class="fa-solid fa-plane-departure text-primary fs-2"></i>
                                <div class="w-100 border-top my-3 border-secondary border-dashed" style="border-style: dashed; border-width: 2px;"></div>
                                <span class="badge bg-secondary-subtle text-secondary small px-3 py-1 rounded-pill">Langsung</span>
                            </div>
                        </div>
                        
                        <div class="col-md-5 text-center text-md-end">
                            <span class="text-muted small d-block mb-1">BANDARA TUJUAN</span>
                            <h4 class="fw-bold text-primary mb-1"><?= htmlspecialchars($flight['kode_bandara_tujuan']) ?></h4>
                            <h6 class="fw-semibold text-secondary mb-2"><?= htmlspecialchars($flight['nama_bandara_tujuan']) ?></h6>
                            <p class="text-muted mb-0"><i class="fa-solid fa-calendar me-2"></i><?= date('d M Y', strtotime($flight['tanggal_berangkat'])) ?></p>
                            <p class="text-muted mb-0"><i class="fa-solid fa-clock me-2"></i>Jam <?= substr($flight['jam_tiba'], 0, 5) ?> WIB</p>
                        </div>
                    </div>

                    <div class="row border-top border-bottom border-light py-4 my-4 g-3 bg-light rounded-4 px-3">
                        <div class="col-sm-6">
                            <h6 class="text-muted small fw-semibold mb-1">MEMBERIKAN KELEBIHAN:</h6>
                            <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                                <li><i class="fa-solid fa-circle-check text-success me-2"></i>Bagasi Kabin 7kg</li>
                                <li><i class="fa-solid fa-circle-check text-success me-2"></i>Bagasi Tercatat 20kg</li>
                                <li><i class="fa-solid fa-circle-check text-success me-2"></i>Hiburan di Pesawat</li>
                            </ul>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <h6 class="text-muted small fw-semibold mb-1">STATUS KURSI:</h6>
                            <span class="fs-5 fw-bold text-danger"><?= $flight['kursi_tersedia'] ?> Kursi Tersedia</span>
                            <small class="text-muted d-block mt-1">Jangan lewatkan perjalanan terbaik Anda!</small>
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center mt-5">
                        <div class="mb-3 mb-sm-0 text-center text-sm-start">
                            <span class="text-muted small d-block">Total Tarif Perjalanan</span>
                            <small class="text-muted text-decoration-line-through">Rp <?= number_format($flight['harga'], 0, ',', '.') ?></small>
                            <h2 class="fw-bold text-success mb-0">Rp <?= number_format($flight['harga_diskon'], 0, ',', '.') ?><span class="fs-6 text-muted fw-normal">/orang</span></h2>
                        </div>
                        
                        <div>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <a href="pemesanan.php?id=<?= $flight['id_penerbangan'] ?>" class="btn btn-primary btn-lg px-5 py-3 fw-bold">
                                    Pesan Sekarang <i class="fa-solid fa-arrow-right ms-2"></i>
                                </a>
                            <?php else: ?>
                                <?php $_SESSION['redirect_after_login'] = BASE_URL . "customer/detail.php?id=" . $flight['id_penerbangan']; ?>
                                <a href="<?= BASE_URL ?>auth/login.php" class="btn btn-primary btn-lg px-5 py-3 fw-bold">
                                    Masuk untuk Memesan <i class="fa-solid fa-arrow-right-to-bracket ms-2"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center">
                <a href="cari_tiket.php" class="btn btn-link text-decoration-none text-muted"><i class="fa-solid fa-arrow-left me-2"></i>Kembali ke Hasil Pencarian</a>
            </div>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
