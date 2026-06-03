<?php
$page_title = "Kelola Penerbangan";
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

$error_msg = null;
$success_msg = null;

// Fetch Dropdowns (Maskapai & Bandara)
$airlines = [];
$airports = [];
try {
    $airlines = $db->query("SELECT * FROM maskapai WHERE status = 'aktif' ORDER BY nama_maskapai ASC")->fetchAll(PDO::FETCH_ASSOC);
    $airports = $db->query("SELECT * FROM bandara WHERE status = 'aktif' ORDER BY kota ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

// Handle CRUD Operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add Flight
    if (isset($_POST['add_flight'])) {
        $id_maskapai = intval($_POST['id_maskapai']);
        $asal = intval($_POST['bandara_asal']);
        $tujuan = intval($_POST['bandara_tujuan']);
        $tanggal_berangkat = $_POST['tanggal_berangkat'];
        $tanggal_tiba = $_POST['tanggal_tiba'];
        $harga = floatval($_POST['harga']);
        $kursi = intval($_POST['kursi_tersedia']);
        $status = $_POST['status'];

        if ($id_maskapai <= 0 || $asal <= 0 || $tujuan <= 0 || empty($tanggal_berangkat) || empty($tanggal_tiba) || $harga <= 0 || $kursi < 0) {
            $error_msg = "Semua kolom harus diisi dengan benar!";
        } elseif ($asal === $tujuan) {
            $error_msg = "Bandara asal dan tujuan tidak boleh sama!";
        } else {
            try {
                $query = "INSERT INTO penerbangan (id_maskapai, bandara_asal, bandara_tujuan, tanggal_berangkat, tanggal_tiba, harga, kursi_tersedia, status) 
                          VALUES (:id_maskapai, :asal, :tujuan, :tanggal_berangkat, :tanggal_tiba, :harga, :kursi, :status)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id_maskapai', $id_maskapai);
                $stmt->bindParam(':asal', $asal);
                $stmt->bindParam(':tujuan', $tujuan);
                $stmt->bindParam(':tanggal_berangkat', $tanggal_berangkat);
                $stmt->bindParam(':tanggal_tiba', $tanggal_tiba);
                $stmt->bindParam(':harga', $harga);
                $stmt->bindParam(':kursi', $kursi);
                $stmt->bindParam(':status', $status);

                if ($stmt->execute()) {
                    $success_msg = "Penerbangan baru berhasil ditambahkan!";
                } else {
                    $error_msg = "Gagal menambahkan penerbangan.";
                }
            } catch (PDOException $e) {
                $error_msg = "Kesalahan database: " . $e->getMessage();
            }
        }
    }

    // Edit Flight details
    if (isset($_POST['edit_flight'])) {
        $id = intval($_POST['id_penerbangan']);
        $id_maskapai = intval($_POST['id_maskapai']);
        $asal = intval($_POST['bandara_asal']);
        $tujuan = intval($_POST['bandara_tujuan']);
        $tanggal_berangkat = $_POST['tanggal_berangkat'];
        $tanggal_tiba = $_POST['tanggal_tiba'];
        $harga = floatval($_POST['harga']);
        $kursi = intval($_POST['kursi_tersedia']);
        $status = $_POST['status'];

        if ($id <= 0 || $id_maskapai <= 0 || $asal <= 0 || $tujuan <= 0 || empty($tanggal_berangkat) || empty($tanggal_tiba) || $harga <= 0 || $kursi < 0) {
            $error_msg = "Semua kolom harus diisi dengan benar!";
        } elseif ($asal === $tujuan) {
            $error_msg = "Bandara asal dan tujuan tidak boleh sama!";
        } else {
            try {
                $query = "UPDATE penerbangan SET id_maskapai = :id_maskapai, bandara_asal = :asal, bandara_tujuan = :tujuan, 
                                                 tanggal_berangkat = :tanggal_berangkat, tanggal_tiba = :tanggal_tiba, 
                                                 harga = :harga, kursi_tersedia = :kursi, status = :status 
                          WHERE id_penerbangan = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id_maskapai', $id_maskapai);
                $stmt->bindParam(':asal', $asal);
                $stmt->bindParam(':tujuan', $tujuan);
                $stmt->bindParam(':tanggal_berangkat', $tanggal_berangkat);
                $stmt->bindParam(':tanggal_tiba', $tanggal_tiba);
                $stmt->bindParam(':harga', $harga);
                $stmt->bindParam(':kursi', $kursi);
                $stmt->bindParam(':status', $status);
                $stmt->bindParam(':id', $id);

                if ($stmt->execute()) {
                    $success_msg = "Detail penerbangan berhasil diperbarui!";
                } else {
                    $error_msg = "Gagal memperbarui penerbangan.";
                }
            } catch (PDOException $e) {
                $error_msg = "Kesalahan database: " . $e->getMessage();
            }
        }
    }

    // Quick Update Price (Calls Stored Procedure `update_harga_penerbangan`)
    if (isset($_POST['update_price_sp'])) {
        $id = intval($_POST['id_penerbangan']);
        $harga_baru = floatval($_POST['harga_baru']);

        if ($id <= 0 || $harga_baru <= 0) {
            $error_msg = "Data harga tidak valid!";
        } else {
            try {
                // Call Stored Procedure
                $query = "CALL update_harga_penerbangan(:id, :harga)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $id);
                $stmt->bindParam(':harga', $harga_baru);
                
                if ($stmt->execute()) {
                    $success_msg = "Harga penerbangan berhasil diubah via Stored Procedure!";
                } else {
                    $error_msg = "Gagal mengubah harga penerbangan.";
                }
            } catch (PDOException $e) {
                $error_msg = "Kesalahan database: " . $e->getMessage();
            }
        }
    }
}

// Delete Flight
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    try {
        $query = "DELETE FROM penerbangan WHERE id_penerbangan = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            $success_msg = "Penerbangan berhasil dihapus!";
        } else {
            $error_msg = "Gagal menghapus penerbangan.";
        }
    } catch (PDOException $e) {
        $error_msg = "Kesalahan database atau dependensi data: " . $e->getMessage();
    }
}

// Fetch single Flight for Edit mode
$edit_data = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    try {
        $query = "SELECT * FROM penerbangan WHERE id_penerbangan = :id LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {}
}

// List Flights (Calls Stored Procedure `tampil_penerbangan`)
$flights = [];
try {
    // Call Stored Procedure to fetch list
    $query = "CALL tampil_penerbangan()";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $flights = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Fallback if SP fails or doesn't exist
    try {
        $query = "SELECT * FROM vw_jadwal_penerbangan ORDER BY tanggal_berangkat ASC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $flights = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $ex) {}
}
?>

<div class="admin-wrapper">
    <!-- Include Sidebar -->
    <?php require_once '../../includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <main class="admin-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Kelola Penerbangan</h3>
                <p class="text-muted mb-0">Manajemen jadwal, harga, dan alokasi kursi penerbangan.</p>
            </div>
        </div>

        <?php if ($error_msg): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i> <?= $error_msg ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($success_msg): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i> <?= $success_msg ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Form Card (Add/Edit) -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h5 class="fw-bold text-secondary mb-3 pb-2 border-bottom">
                        <?= $edit_data ? '<i class="fa-solid fa-pen-to-square text-warning me-2"></i> Edit Jadwal' : '<i class="fa-solid fa-square-plus text-primary me-2"></i> Tambah Penerbangan' ?>
                    </h5>
                    
                    <form action="index.php" method="POST">
                        <?php if ($edit_data): ?>
                            <input type="hidden" name="id_penerbangan" value="<?= $edit_data['id_penerbangan'] ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="id_maskapai" class="form-label fw-semibold">Maskapai</label>
                            <select class="form-select bg-light" id="id_maskapai" name="id_maskapai" required>
                                <option value="" selected disabled>Pilih Maskapai...</option>
                                <?php foreach ($airlines as $al): ?>
                                    <option value="<?= $al['id_maskapai'] ?>" <?= (isset($edit_data['id_maskapai']) && intval($edit_data['id_maskapai']) === intval($al['id_maskapai'])) ? 'selected' : '' ?>><?= htmlspecialchars($al['nama_maskapai']) ?> (<?= htmlspecialchars($al['kode_maskapai']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="bandara_asal" class="form-label fw-semibold">Bandara Asal</label>
                            <select class="form-select bg-light" id="bandara_asal" name="bandara_asal" required>
                                <option value="" selected disabled>Pilih Asal...</option>
                                <?php foreach ($airports as $ap): ?>
                                    <option value="<?= $ap['id_bandara'] ?>" <?= (isset($edit_data['bandara_asal']) && intval($edit_data['bandara_asal']) === intval($ap['id_bandara'])) ? 'selected' : '' ?>><?= htmlspecialchars($ap['kota']) ?> (<?= htmlspecialchars($ap['kode_bandara']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="bandara_tujuan" class="form-label fw-semibold">Bandara Tujuan</label>
                            <select class="form-select bg-light" id="bandara_tujuan" name="bandara_tujuan" required>
                                <option value="" selected disabled>Pilih Tujuan...</option>
                                <?php foreach ($airports as $ap): ?>
                                    <option value="<?= $ap['id_bandara'] ?>" <?= (isset($edit_data['bandara_tujuan']) && intval($edit_data['bandara_tujuan']) === intval($ap['id_bandara'])) ? 'selected' : '' ?>><?= htmlspecialchars($ap['kota']) ?> (<?= htmlspecialchars($ap['kode_bandara']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="tanggal_berangkat" class="form-label fw-semibold">Tanggal Berangkat</label>
                            <input type="datetime-local" class="form-control bg-light" id="tanggal_berangkat" name="tanggal_berangkat" value="<?= isset($edit_data['tanggal_berangkat']) ? date('Y-m-d\TH:i', strtotime($edit_data['tanggal_berangkat'])) : '' ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="tanggal_tiba" class="form-label fw-semibold">Tanggal Tiba</label>
                            <input type="datetime-local" class="form-control bg-light" id="tanggal_tiba" name="tanggal_tiba" value="<?= isset($edit_data['tanggal_tiba']) ? date('Y-m-d\TH:i', strtotime($edit_data['tanggal_tiba'])) : '' ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="harga" class="form-label fw-semibold">Harga Tiket (Rp)</label>
                            <input type="number" class="form-control bg-light" id="harga" name="harga" value="<?= htmlspecialchars($edit_data['harga'] ?? '') ?>" placeholder="Contoh: 1500000" min="0" required>
                        </div>

                        <div class="mb-3">
                            <label for="kursi_tersedia" class="form-label fw-semibold">Kursi Tersedia</label>
                            <input type="number" class="form-control bg-light" id="kursi_tersedia" name="kursi_tersedia" value="<?= htmlspecialchars($edit_data['kursi_tersedia'] ?? '150') ?>" min="0" required>
                        </div>

                        <div class="mb-4">
                            <label for="status" class="form-label fw-semibold">Status Penerbangan</label>
                            <select class="form-select bg-light" id="status" name="status" required>
                                <option value="tersedia" <?= (isset($edit_data['status']) && $edit_data['status'] === 'tersedia') ? 'selected' : '' ?>>Tersedia</option>
                                <option value="penuh" <?= (isset($edit_data['status']) && $edit_data['status'] === 'penuh') ? 'selected' : '' ?>>Penuh</option>
                                <option value="dibatalkan" <?= (isset($edit_data['status']) && $edit_data['status'] === 'dibatalkan') ? 'selected' : '' ?>>Dibatalkan</option>
                            </select>
                        </div>

                        <button type="submit" name="<?= $edit_data ? 'edit_flight' : 'add_flight' ?>" class="btn <?= $edit_data ? 'btn-warning' : 'btn-primary' ?> w-100 fw-bold">
                            <?= $edit_data ? '<i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan' : '<i class="fa-solid fa-plus me-1"></i> Tambah Penerbangan' ?>
                        </button>
                        
                        <?php if ($edit_data): ?>
                            <a href="index.php" class="btn btn-outline-secondary w-100 fw-bold mt-2">Batal</a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Price-Only Quick Update Form via SP -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <h5 class="fw-bold text-secondary mb-3 pb-2 border-bottom">
                        <i class="fa-solid fa-tags text-success me-2"></i> Update Harga Cepat (SP)
                    </h5>
                    <form action="index.php" method="POST">
                        <div class="mb-3">
                            <label for="sp_id" class="form-label fw-semibold">Pilih ID Penerbangan</label>
                            <input type="number" class="form-control bg-light" id="sp_id" name="id_penerbangan" placeholder="Contoh: 1" required>
                        </div>
                        <div class="mb-4">
                            <label for="sp_price" class="form-label fw-semibold">Harga Baru (Rp)</label>
                            <input type="number" class="form-control bg-light" id="sp_price" name="harga_baru" placeholder="Contoh: 1850000" required>
                        </div>
                        <button type="submit" name="update_price_sp" class="btn btn-success w-100 fw-bold">
                            <i class="fa-solid fa-bolt me-1"></i> Update Harga via SP
                        </button>
                    </form>
                </div>
            </div>

            <!-- List Card -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <h5 class="fw-bold text-secondary mb-3 border-bottom pb-2">Daftar Jadwal Penerbangan (dari SP tampil_penerbangan)</h5>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle" style="font-size: 0.9rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Maskapai</th>
                                    <th>Rute</th>
                                    <th>Waktu Keberangkatan</th>
                                    <th>Harga</th>
                                    <th>Sisa Kursi</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($flights)): ?>
                                    <?php foreach ($flights as $f): ?>
                                        <tr>
                                            <td><?= $f['id_penerbangan'] ?></td>
                                            <td>
                                                <strong class="text-dark d-block"><?= htmlspecialchars($f['nama_maskapai']) ?></strong>
                                                <span class="badge bg-secondary-subtle text-secondary"><?= htmlspecialchars($f['kode_maskapai']) ?></span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-1 font-monospace fw-bold">
                                                    <span><?= htmlspecialchars($f['kode_bandara_asal']) ?></span>
                                                    <i class="fa-solid fa-arrow-right text-muted" style="font-size:0.75rem;"></i>
                                                    <span><?= htmlspecialchars($f['kode_bandara_tujuan']) ?></span>
                                                </div>
                                                <small class="text-muted text-capitalize"><?= htmlspecialchars($f['kota_asal']) ?> ke <?= htmlspecialchars($f['kota_tujuan']) ?></small>
                                            </td>
                                            <td>
                                                <span class="fw-semibold d-block text-primary"><?= date('H:i', strtotime($f['tanggal_berangkat'])) ?></span>
                                                <small class="text-muted"><?= date('d M Y', strtotime($f['tanggal_berangkat'])) ?></small>
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
                                                <span class="badge <?= $f['status'] === 'tersedia' ? 'bg-success-subtle text-success' : ($f['status'] === 'penuh' ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger') ?> px-2.5 py-1.5 rounded-pill text-capitalize">
                                                    <?= htmlspecialchars($f['status']) ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a href="index.php?action=edit&id=<?= $f['id_penerbangan'] ?>" class="btn btn-outline-warning btn-sm me-1" title="Edit">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                                <a href="index.php?action=delete&id=<?= $f['id_penerbangan'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus penerbangan ini?');" title="Hapus">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">Jadwal penerbangan tidak ditemukan.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php 
$is_admin_layout = true;
require_once '../../includes/footer.php'; 
?>
