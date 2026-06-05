<?php
$page_title = "Penerbangan Wilayah Barat";
require_once '../../includes/header.php';

// Authentication & Role Check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error'] = "Akses ditolak! Anda harus masuk sebagai Admin.";
    header("Location: ../../auth/login.php");
    exit();
}

require_once '../../config/database.php';
$database = new Database();
$db = $database->getConnection();

$flights = [];
try {
    // Querying database view `penerbangan_barat`
    $query = "SELECT * FROM penerbangan_barat ORDER BY tanggal_berangkat ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $flights = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
}
?>

<div class="admin-wrapper">
    <!-- Include Sidebar -->
    <?php require_once '../../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="admin-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1"><i class="fa-solid fa-compass text-warning me-2"></i> Penerbangan Wilayah Barat
                </h3>
                <p class="text-muted mb-0">Menampilkan data fragmentasi horizontal dari view
                    <code>penerbangan_barat</code> (Asal: Jakarta, Lampung, Palembang).</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID Flight</th>
                            <th>Maskapai</th>
                            <th>Rute (Kota Asal & Tujuan)</th>
                            <th>Waktu Keberangkatan</th>
                            <th>Harga</th>
                            <th>Kursi Tersedia</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($flights)): ?>
                            <?php foreach ($flights as $f): ?>
                                <tr>
                                    <td><?= $f['id_penerbangan'] ?></td>
                                    <td>
                                        <strong class="text-dark d-block"><?= htmlspecialchars($f['nama_maskapai']) ?></strong>
                                        <span
                                            class="badge bg-secondary-subtle text-secondary"><?= htmlspecialchars($f['kode_maskapai']) ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1 font-monospace fw-bold text-warning">
                                            <span><?= htmlspecialchars($f['kode_bandara_asal']) ?></span>
                                            <i class="fa-solid fa-arrow-right text-muted" style="font-size:0.75rem;"></i>
                                            <span><?= htmlspecialchars($f['kode_bandara_tujuan']) ?></span>
                                        </div>
                                        <small class="text-muted text-capitalize"><?= htmlspecialchars($f['kota_asal']) ?> ke
                                            <?= htmlspecialchars($f['kota_tujuan']) ?></small>
                                    </td>
                                    <td>
                                        <span
                                            class="fw-semibold d-block text-primary"><?= date('H:i', strtotime($f['tanggal_berangkat'])) ?></span>
                                        <small
                                            class="text-muted"><?= date('d M Y', strtotime($f['tanggal_berangkat'])) ?></small>
                                    </td>
                                    <td class="fw-bold text-success">
                                        Rp <?= number_format($f['harga'], 0, ',', '.') ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark fw-bold border">
                                            <?= $f['kursi_tersedia'] ?> Kursi
                                        </span>
                                    </td>
                                    <td>
                                        <?php $status = $f['status_penerbangan'] ?? null; ?>
                                        <span
                                            class="badge <?= $status === 'aktif' ? 'bg-success-subtle text-success' : ($status === 'dibatalkan' ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning') ?> px-2 py-1 rounded-pill text-capitalize">
                                            <?= htmlspecialchars($status ?? 'N/A') ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Tidak ada jadwal penerbangan Barat saat
                                    ini.</td>
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
require_once '../../includes/footer.php';
?>