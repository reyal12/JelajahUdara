<?php
$page_title = "Jelajahi Indonesia Bersama JelajahUdara";
require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Fetch airports for options
$airports = [];
try {
    $query_airport = "SELECT * FROM bandara WHERE status = 'aktif' ORDER BY kota ASC";
    $stmt_airport = $db->prepare($query_airport);
    $stmt_airport->execute();
    $airports = $stmt_airport->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Silent fallback if table does not exist yet
}

// Fetch promos using custom function hitung_diskon()
$promos = [];
try {
    $query_promo = "SELECT p.*, m.nama_maskapai, m.kode_maskapai, 
                           b_asal.kota AS kota_asal, b_asal.kode_bandara AS kode_asal,
                           b_tuj.kota AS kota_tujuan, b_tuj.kode_bandara AS kode_tujuan,
                           hitung_diskon(p.harga) AS harga_diskon
                    FROM penerbangan p
                    JOIN maskapai m ON p.id_maskapai = m.id_maskapai
                    JOIN bandara b_asal ON p.bandara_asal = b_asal.id_bandara
                    JOIN bandara b_tuj ON p.bandara_tujuan = b_tuj.id_bandara
                    WHERE p.status = 'tersedia' AND p.kursi_tersedia > 0
                    LIMIT 3";
    $stmt_promo = $db->prepare($query_promo);
    $stmt_promo->execute();
    $promos = $stmt_promo->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // If custom function doesn't exist, calculate discount in PHP as fallback
    try {
        $query_promo = "SELECT p.*, m.nama_maskapai, m.kode_maskapai, 
                               b_asal.kota AS kota_asal, b_asal.kode_bandara AS kode_asal,
                               b_tuj.kota AS kota_tujuan, b_tuj.kode_bandara AS kode_tujuan
                        FROM penerbangan p
                        JOIN maskapai m ON p.id_maskapai = m.id_maskapai
                        JOIN bandara b_asal ON p.bandara_asal = b_asal.id_bandara
                        JOIN bandara b_tuj ON p.bandara_tujuan = b_tuj.id_bandara
                        WHERE p.status = 'tersedia' AND p.kursi_tersedia > 0
                        LIMIT 3";
        $stmt_promo = $db->prepare($query_promo);
        $stmt_promo->execute();
        $promos_raw = $stmt_promo->fetchAll(PDO::FETCH_ASSOC);
        foreach ($promos_raw as $p) {
            $p['harga_diskon'] = $p['harga'] * 0.90;
            $promos[] = $p;
        }
    } catch (PDOException $ex) {}
}

// Fetch active maskapai
$airlines = [];
try {
    $query_maskapai = "SELECT * FROM maskapai WHERE status = 'aktif' LIMIT 6";
    $stmt_maskapai = $db->prepare($query_maskapai);
    $stmt_maskapai->execute();
    $airlines = $stmt_maskapai->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
?>

<!-- Hero Section -->
<section class="hero-section bg-gradient-primary text-center">
    <div class="container py-4">
        <div class="row align-items-center justify-content-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3">Jelajahi Indonesia Bersama <span class="text-info">JelajahUdara</span></h1>
                <p class="lead mb-4 text-white-50">Temukan tiket pesawat murah, aman, dan nyaman ke seluruh penjuru Nusantara dengan cepat.</p>
                <div class="d-flex justify-content-center gap-2 mb-4">
                    <span class="badge bg-light text-dark px-3 py-2"><i class="fa-solid fa-shield-halved text-success me-1"></i> Asuransi Perjalanan</span>
                    <span class="badge bg-light text-dark px-3 py-2"><i class="fa-solid fa-headset text-primary me-1"></i> Layanan 24/7</span>
                    <span class="badge bg-light text-dark px-3 py-2"><i class="fa-solid fa-tag text-danger me-1"></i> Promo Terpercaya</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Search Ticket Card -->
<section class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="search-card">
                <h5 class="fw-bold mb-4"><i class="fa-solid fa-plane-departure text-primary me-2"></i> Cari Penerbangan</h5>
                <form action="<?= BASE_URL ?>customer/cari_tiket.php" method="GET">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="asal" class="form-label text-muted small fw-semibold">Kota Asal</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-plane-departure text-muted"></i></span>
                                <select class="form-select bg-light border-start-0 ps-0" id="asal" name="asal" required>
                                    <option value="" selected disabled>Pilih asal...</option>
                                    <?php foreach ($airports as $ap): ?>
                                        <option value="<?= $ap['id_bandara'] ?>"><?= htmlspecialchars($ap['kota']) ?> (<?= htmlspecialchars($ap['kode_bandara']) ?>) - <?= htmlspecialchars($ap['nama_bandara']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="tujuan" class="form-label text-muted small fw-semibold">Kota Tujuan</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-plane-arrival text-muted"></i></span>
                                <select class="form-select bg-light border-start-0 ps-0" id="tujuan" name="tujuan" required>
                                    <option value="" selected disabled>Pilih tujuan...</option>
                                    <?php foreach ($airports as $ap): ?>
                                        <option value="<?= $ap['id_bandara'] ?>"><?= htmlspecialchars($ap['kota']) ?> (<?= htmlspecialchars($ap['kode_bandara']) ?>) - <?= htmlspecialchars($ap['nama_bandara']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="tanggal" class="form-label text-muted small fw-semibold">Tanggal Berangkat</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-calendar text-muted"></i></span>
                                <input type="date" class="form-control bg-light border-start-0 ps-0" id="tanggal" name="tanggal" required min="<?= date('Y-m-d') ?>">
                            </div>
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="fa-solid fa-magnifying-glass me-2"></i> Cari Penerbangan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Promo Section -->
<section id="promo" class="container py-5">
    <div class="text-center mb-5">
        <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill fw-bold mb-2">Penawaran Menarik</span>
        <h2 class="fw-bold">Promo Tiket Pesawat Spesial</h2>
        <p class="text-muted">Nikmati potongan harga eksklusif untuk destinasi favorit Anda di Indonesia</p>
    </div>

    <div class="row g-4 justify-content-center">
        <?php if (!empty($promos)): ?>
            <?php foreach ($promos as $p): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card custom-card h-100 position-relative">
                        <span class="promo-badge">Diskon 10%</span>
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-3 text-primary" style="font-size: 1.5rem;">
                                    <i class="fa-solid fa-plane"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0"><?= htmlspecialchars($p['nama_maskapai']) ?></h6>
                                    <small class="text-muted"><?= htmlspecialchars($p['kode_maskapai']) ?></small>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center my-3 bg-light p-3 rounded-3">
                                <div>
                                    <h5 class="fw-bold mb-0 text-primary"><?= htmlspecialchars($p['kota_asal']) ?></h5>
                                    <small class="text-muted"><?= htmlspecialchars($p['kode_asal']) ?></small>
                                </div>
                                <i class="fa-solid fa-arrow-right text-muted mx-2"></i>
                                <div class="text-end">
                                    <h5 class="fw-bold mb-0 text-primary"><?= htmlspecialchars($p['kota_tujuan']) ?></h5>
                                    <small class="text-muted"><?= htmlspecialchars($p['kode_tujuan']) ?></small>
                                </div>
                            </div>
                            
                            <div class="mt-auto pt-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-muted text-decoration-line-through d-block">Rp <?= number_format($p['harga'], 0, ',', '.') ?></small>
                                        <h5 class="fw-bold text-success mb-0">Rp <?= number_format($p['harga_diskon'], 0, ',', '.') ?></h5>
                                    </div>
                                    <a href="<?= BASE_URL ?>customer/detail.php?id=<?= $p['id_penerbangan'] ?>" class="btn btn-primary btn-sm">Pesan</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-4">
                <div class="card p-5 border-0 shadow-sm bg-white rounded-4">
                    <i class="fa-solid fa-receipt text-muted fs-1 mb-3"></i>
                    <p class="text-muted mb-0">Belum ada promo penerbangan aktif saat ini.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Maskapai Populer Section -->
<section class="bg-white py-5 border-top border-bottom border-light">
    <div class="container py-3">
        <div class="text-center mb-5">
            <h3 class="fw-bold">Bekerja Sama dengan Maskapai Terbaik</h3>
            <p class="text-muted">Terbang aman dengan maskapai pilihan terpopuler di Indonesia</p>
        </div>
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-4 justify-content-center align-items-center text-center">
            <?php if (!empty($airlines)): ?>
                <?php foreach ($airlines as $al): ?>
                    <div class="col">
                        <div class="p-3 bg-light rounded-4 custom-card d-flex flex-column align-items-center justify-content-center" style="min-height: 100px;">
                            <i class="fa-solid fa-plane-up text-primary fs-3 mb-2"></i>
                            <h6 class="fw-bold mb-0 text-secondary"><?= htmlspecialchars($al['nama_maskapai']) ?></h6>
                            <span class="badge bg-secondary-subtle text-secondary small px-2 mt-1"><?= htmlspecialchars($al['kode_maskapai']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <p class="text-muted mb-0">Daftar maskapai belum tersedia.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Keunggulan Layanan Section -->
<section id="tentang-kami" class="container py-5 my-3">
    <div class="text-center mb-5">
        <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-bold mb-2">Kenapa JelajahUdara?</span>
        <h2 class="fw-bold">Keunggulan Layanan Kami</h2>
        <p class="text-muted">Kami berkomitmen memberikan kenyamanan dan keamanan transaksi tiket Anda</p>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card custom-card border-0 p-4 text-center h-100">
                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle mx-auto mb-4" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-bolt fs-2"></i>
                </div>
                <h5 class="fw-bold">Pencarian Instan & Akurat</h5>
                <p class="text-muted mb-0">Cari dan temukan jadwal penerbangan langsung dari berbagai maskapai pilihan dalam hitungan detik.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card custom-card border-0 p-4 text-center h-100">
                <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle mx-auto mb-4" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-lock fs-2"></i>
                </div>
                <h5 class="fw-bold">Transaksi 100% Aman</h5>
                <p class="text-muted mb-0">Sistem pembayaran terenkripsi yang mendukung Transfer Bank, E-Wallet, dan Virtual Account.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card custom-card border-0 p-4 text-center h-100">
                <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle mx-auto mb-4" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-ticket fs-2"></i>
                </div>
                <h5 class="fw-bold">E-Ticket Langsung Cetak</h5>
                <p class="text-muted mb-0">Dapatkan E-Ticket Anda langsung setelah pembayaran dikonfirmasi, siap dicetak kapan saja.</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimoni Section -->
<section class="bg-light py-5">
    <div class="container py-3">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Apa Kata Pelanggan Kami</h2>
            <p class="text-muted">Testimoni nyata dari mereka yang telah menjelajah Indonesia bersama kami</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-4 rounded-4 bg-white">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-secondary bg-opacity-10 rounded-circle text-center p-2 me-3" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-user text-primary"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">Andi Pratama</h6>
                            <small class="text-muted">Traveler - Jakarta</small>
                        </div>
                    </div>
                    <div class="text-warning mb-2">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                    </div>
                    <p class="text-muted italic mb-0">"Pemesanan tiket di JelajahUdara sangat cepat. Hanya butuh beberapa menit, e-ticket sudah terbit dan siap digunakan untuk liburan keluarga kami."</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-4 rounded-4 bg-white">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-secondary bg-opacity-10 rounded-circle text-center p-2 me-3" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-user text-success"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">Dewi Sartika</h6>
                            <small class="text-muted">Pebisnis - Makassar</small>
                        </div>
                    </div>
                    <div class="text-warning mb-2">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star-half-stroke"></i>
                    </div>
                    <p class="text-muted italic mb-0">"Sangat terbantu dengan metode pembayaran Virtual Account yang otomatis terverifikasi. Sangat efisien untuk kebutuhan bisnis dinas luar kota saya."</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-4 rounded-4 bg-white">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-secondary bg-opacity-10 rounded-circle text-center p-2 me-3" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-user text-info"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">Rian Hidayat</h6>
                            <small class="text-muted">Fotografer - Surabaya</small>
                        </div>
                    </div>
                    <div class="text-warning mb-2">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                    </div>
                    <p class="text-muted italic mb-0">"Desain web yang bersih dan modern memudahkan pencarian rute penerbangan timur Indonesia yang biasanya susah didapat. Rekomendasi sekali!"</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
