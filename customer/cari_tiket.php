<?php
$page_title = "Cari Tiket Penerbangan";
require_once '../includes/header.php';
require_once '../includes/navbar.php';
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Fetch airports list for search dropdowns
$airports = [];
try {
    $query_airport = "SELECT * FROM bandara WHERE status = 'aktif' ORDER BY kota ASC";
    $stmt_airport = $db->prepare($query_airport);
    $stmt_airport->execute();
    $airports = $stmt_airport->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

// Get search filters
$asal = isset($_GET['asal']) ? intval($_GET['asal']) : 0;
$tujuan = isset($_GET['tujuan']) ? intval($_GET['tujuan']) : 0;
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';

// Build search query based on vw_jadwal_penerbangan
$flights = [];
$search_executed = false;

if ($asal > 0 || $tujuan > 0 || !empty($tanggal)) {
    $search_executed = true;
    
    // Attempt query using hitung_diskon custom function
    try {
        $query_search = "SELECT *, hitung_diskon(harga) AS harga_diskon 
                         FROM vw_jadwal_penerbangan 
                         WHERE status_penerbangan = 'aktif' AND kursi_tersedia > 0";
        
        $params = [];
        
        if ($asal > 0) {
            $query_search .= " AND asal_bandara = :asal";
            $params[':asal'] = $asal;
        }
        if ($tujuan > 0) {
            $query_search .= " AND tujuan_bandara = :tujuan";
            $params[':tujuan'] = $tujuan;
        }
        if (!empty($tanggal)) {
            $query_search .= " AND DATE(tanggal_berangkat) = :tanggal";
            $params[':tanggal'] = $tanggal;
        }
        
        $query_search .= " ORDER BY harga ASC";
        $stmt_search = $db->prepare($query_search);
        
        foreach ($params as $key => $val) {
            $stmt_search->bindValue($key, $val);
        }
        
        $stmt_search->execute();
        $flights = $stmt_search->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Fallback query if custom function does not exist
        try {
            $query_search = "SELECT * FROM vw_jadwal_penerbangan 
                             WHERE status_penerbangan = 'aktif' AND kursi_tersedia > 0";
            
            $params = [];
            
            if ($asal > 0) {
                $query_search .= " AND asal_bandara = :asal";
                $params[':asal'] = $asal;
            }
            if ($tujuan > 0) {
                $query_search .= " AND tujuan_bandara = :tujuan";
                $params[':tujuan'] = $tujuan;
            }
            if (!empty($tanggal)) {
                $query_search .= " AND DATE(tanggal_berangkat) = :tanggal";
                $params[':tanggal'] = $tanggal;
            }
            
            $query_search .= " ORDER BY harga ASC";
            $stmt_search = $db->prepare($query_search);
            
            foreach ($params as $key => $val) {
                $stmt_search->bindValue($key, $val);
            }
            
            $stmt_search->execute();
            $flights_raw = $stmt_search->fetchAll(PDO::FETCH_ASSOC);
            foreach ($flights_raw as $f) {
                $f['harga_diskon'] = $f['harga'] * 0.90;
                $flights[] = $f;
            }
        } catch (PDOException $ex) {}
    }
} else {
    // If no filter selected, display all available flights
    try {
        $query_all = "SELECT *, hitung_diskon(harga) AS harga_diskon FROM vw_jadwal_penerbangan WHERE status_penerbangan = 'aktif' AND kursi_tersedia > 0 ORDER BY tanggal_berangkat ASC";
        $stmt_all = $db->prepare($query_all);
        $stmt_all->execute();
        $flights = $stmt_all->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        try {
            $query_all = "SELECT * FROM vw_jadwal_penerbangan WHERE status_penerbangan = 'aktif' AND kursi_tersedia > 0 ORDER BY tanggal_berangkat ASC";
            $stmt_all = $db->prepare($query_all);
            $stmt_all->execute();
            $flights_raw = $stmt_all->fetchAll(PDO::FETCH_ASSOC);
            foreach ($flights_raw as $f) {
                $f['harga_diskon'] = $f['harga'] * 0.90;
                $flights[] = $f;
            }
        } catch (PDOException $ex) {}
    }
}
?>

<main class="container my-5">
    <div class="row g-4">
        <!-- Search Filter Column -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white sticky-top" style="top: 100px; z-index: 10;">
                <h5 class="fw-bold mb-4 text-secondary"><i class="fa-solid fa-sliders text-primary me-2"></i> Cari & Filter</h5>
                <form action="" method="GET">
                    <div class="mb-3">
                        <label for="asal" class="form-label text-muted small fw-semibold">Kota Asal</label>
                        <select class="form-select bg-light" id="asal" name="asal">
                            <option value="">Semua Asal</option>
                            <?php foreach ($airports as $ap): ?>
                                <option value="<?= $ap['id_bandara'] ?>" <?= $asal === intval($ap['id_bandara']) ? 'selected' : '' ?>><?= htmlspecialchars($ap['kota']) ?> (<?= htmlspecialchars($ap['kode_bandara']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="tujuan" class="form-label text-muted small fw-semibold">Kota Tujuan</label>
                        <select class="form-select bg-light" id="tujuan" name="tujuan">
                            <option value="">Semua Tujuan</option>
                            <?php foreach ($airports as $ap): ?>
                                <option value="<?= $ap['id_bandara'] ?>" <?= $tujuan === intval($ap['id_bandara']) ? 'selected' : '' ?>><?= htmlspecialchars($ap['kota']) ?> (<?= htmlspecialchars($ap['kode_bandara']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="tanggal" class="form-label text-muted small fw-semibold">Tanggal Berangkat</label>
                        <input type="date" class="form-control bg-light" id="tanggal" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>" min="<?= date('Y-m-d') ?>">
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold">
                        <i class="fa-solid fa-magnifying-glass me-2"></i> Cari Tiket
                    </button>
                    <?php if ($search_executed): ?>
                        <a href="cari_tiket.php" class="btn btn-outline-secondary w-100 py-2.5 fw-bold mt-2">
                            Reset Filter
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Search Results Column -->
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1">Hasil Pencarian</h4>
                    <p class="text-muted mb-0"><?= count($flights) ?> penerbangan tersedia ditemukan</p>
                </div>
            </div>

            <!-- Flight Result Lists -->
            <div class="d-flex flex-column gap-3">
                <?php if (!empty($flights)): ?>
                    <?php foreach ($flights as $f): ?>
                        <div class="ticket-card border-left">
                            <div class="row align-items-center">
                                <div class="col-md-3 mb-3 mb-md-0 text-center text-md-start">
                                    <div class="d-flex align-items-center justify-content-center justify-content-md-start">
                                        <i class="fa-solid fa-plane-up text-primary fs-3 me-2"></i>
                                        <div>
                                            <h6 class="fw-bold mb-0 text-secondary"><?= htmlspecialchars($f['nama_maskapai']) ?></h6>
                                            <small class="badge bg-secondary-subtle text-secondary"><?= htmlspecialchars($f['kode_maskapai']) ?></small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5 mb-3 mb-md-0">
                                    <div class="d-flex align-items-center justify-content-between justify-content-md-around text-center">
                                        <div>
                                            <span class="fs-5 fw-bold text-dark mb-0"><?= htmlspecialchars($f['kode_bandara_asal']) ?></span>
                                            <p class="small text-muted mb-0"><?= htmlspecialchars($f['kota_asal']) ?></p>
                                            <span class="small text-primary fw-semibold"><?= date('H:i', strtotime($f['jam_berangkat'])) ?></span>
                                        </div>
                                        <div class="d-flex flex-column align-items-center px-2">
                                            <small class="text-muted" style="font-size: 0.75rem;">Langsung</small>
                                            <i class="fa-solid fa-arrow-right text-primary my-1"></i>
                                            <small class="text-muted small"><?= date('d M Y', strtotime($f['tanggal_berangkat'])) ?></small>
                                        </div>
                                        <div>
                                            <span class="fs-5 fw-bold text-dark mb-0"><?= htmlspecialchars($f['kode_bandara_tujuan']) ?></span>
                                            <p class="small text-muted mb-0"><?= htmlspecialchars($f['kota_tujuan']) ?></p>
                                            <span class="small text-primary fw-semibold"><?= date('H:i', strtotime($f['jam_tiba'])) ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-center text-md-end">
                                    <div class="pe-md-3">
                                        <div class="mb-2">
                                            <small class="text-muted text-decoration-line-through d-block mb-0">Rp <?= number_format($f['harga'], 0, ',', '.') ?></small>
                                            <h4 class="fw-bold text-success mb-0">Rp <?= number_format($f['harga_diskon'], 0, ',', '.') ?></h4>
                                            <small class="text-muted small d-block">Kursi: <strong class="text-danger"><?= $f['kursi_tersedia'] ?> tersisa</strong></small>
                                        </div>
                                        <a href="<?= BASE_URL ?>customer/detail.php?id=<?= $f['id_penerbangan'] ?>" class="btn btn-primary w-100 py-2">
                                            <i class="fa-solid fa-circle-info me-2"></i> Pilih Penerbangan
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="card border-0 shadow-sm p-5 text-center bg-white rounded-4">
                        <i class="fa-solid fa-plane-slash text-muted display-1 mb-4"></i>
                        <h4 class="fw-bold">Tidak Ada Penerbangan</h4>
                        <p class="text-muted">Maaf, kami tidak menemukan penerbangan yang sesuai dengan filter pencarian Anda. Silakan cari tanggal atau kota asal/tujuan lainnya.</p>
                        <a href="cari_tiket.php" class="btn btn-primary px-4 py-2 mt-2 align-self-center"><i class="fa-solid fa-rotate-left me-2"></i> Lihat Semua Rute</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
