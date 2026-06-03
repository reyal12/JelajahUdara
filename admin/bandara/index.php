<?php
$page_title = "Kelola Bandara";
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

// Handle CRUD Operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add Bandara
    if (isset($_POST['add_bandara'])) {
        $nama = filter_input(INPUT_POST, 'nama_bandara', FILTER_SANITIZE_SPECIAL_CHARS);
        $kode = filter_input(INPUT_POST, 'kode_bandara', FILTER_SANITIZE_SPECIAL_CHARS);
        $kota = filter_input(INPUT_POST, 'kota', FILTER_SANITIZE_SPECIAL_CHARS);
        $wilayah = filter_input(INPUT_POST, 'wilayah', FILTER_SANITIZE_SPECIAL_CHARS);
        $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS);

        if (empty($nama) || empty($kode) || empty($kota) || empty($wilayah) || empty($status)) {
            $error_msg = "Semua kolom harus diisi!";
        } else {
            try {
                $query = "INSERT INTO bandara (nama_bandara, kode_bandara, kota, wilayah, status) 
                          VALUES (:nama, :kode, :kota, :wilayah, :status)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':nama', $nama);
                $stmt->bindParam(':kode', $kode);
                $stmt->bindParam(':kota', $kota);
                $stmt->bindParam(':wilayah', $wilayah);
                $stmt->bindParam(':status', $status);
                
                if ($stmt->execute()) {
                    $success_msg = "Bandara baru berhasil ditambahkan!";
                } else {
                    $error_msg = "Gagal menambahkan bandara.";
                }
            } catch (PDOException $e) {
                $error_msg = "Kesalahan database: " . $e->getMessage();
            }
        }
    }

    // Edit Bandara
    if (isset($_POST['edit_bandara'])) {
        $id = intval($_POST['id_bandara']);
        $nama = filter_input(INPUT_POST, 'nama_bandara', FILTER_SANITIZE_SPECIAL_CHARS);
        $kode = filter_input(INPUT_POST, 'kode_bandara', FILTER_SANITIZE_SPECIAL_CHARS);
        $kota = filter_input(INPUT_POST, 'kota', FILTER_SANITIZE_SPECIAL_CHARS);
        $wilayah = filter_input(INPUT_POST, 'wilayah', FILTER_SANITIZE_SPECIAL_CHARS);
        $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS);

        if (empty($nama) || empty($kode) || empty($kota) || empty($wilayah) || empty($status)) {
            $error_msg = "Semua kolom harus diisi!";
        } else {
            try {
                $query = "UPDATE bandara SET nama_bandara = :nama, kode_bandara = :kode, kota = :kota, wilayah = :wilayah, status = :status WHERE id_bandara = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':nama', $nama);
                $stmt->bindParam(':kode', $kode);
                $stmt->bindParam(':kota', $kota);
                $stmt->bindParam(':wilayah', $wilayah);
                $stmt->bindParam(':status', $status);
                $stmt->bindParam(':id', $id);

                if ($stmt->execute()) {
                    $success_msg = "Bandara berhasil diperbarui!";
                } else {
                    $error_msg = "Gagal memperbarui bandara.";
                }
            } catch (PDOException $e) {
                $error_msg = "Kesalahan database: " . $e->getMessage();
            }
        }
    }
}

// Delete Bandara
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    try {
        $query = "DELETE FROM bandara WHERE id_bandara = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            $success_msg = "Bandara berhasil dihapus!";
        } else {
            $error_msg = "Gagal menghapus bandara.";
        }
    } catch (PDOException $e) {
        $error_msg = "Kesalahan database atau dependensi data: " . $e->getMessage();
    }
}

// Fetch single Bandara for Edit mode
$edit_data = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    try {
        $query = "SELECT * FROM bandara WHERE id_bandara = :id LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {}
}

// Search & List Bandara
$search = isset($_GET['search']) ? filter_input(INPUT_GET, 'search', FILTER_SANITIZE_SPECIAL_CHARS) : '';
$airports = [];
try {
    if (!empty($search)) {
        $query = "SELECT * FROM bandara 
                  WHERE nama_bandara LIKE :search 
                     OR kode_bandara LIKE :search 
                     OR kota LIKE :search 
                     OR wilayah LIKE :search 
                  ORDER BY kota ASC";
        $stmt = $db->prepare($query);
        $search_param = "%" . $search . "%";
        $stmt->bindParam(':search', $search_param);
    } else {
        $query = "SELECT * FROM bandara ORDER BY kota ASC";
        $stmt = $db->prepare($query);
    }
    $stmt->execute();
    $airports = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
?>

<div class="admin-wrapper">
    <!-- Include Sidebar -->
    <?php require_once '../../includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <main class="admin-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Kelola Bandara</h3>
                <p class="text-muted mb-0">Manajemen bandara dan wilayah rute JelajahUdara.</p>
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
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <h5 class="fw-bold text-secondary mb-3 pb-2 border-bottom">
                        <?= $edit_data ? '<i class="fa-solid fa-pen-to-square text-warning me-2"></i> Edit Bandara' : '<i class="fa-solid fa-square-plus text-primary me-2"></i> Tambah Bandara' ?>
                    </h5>
                    
                    <form action="index.php" method="POST">
                        <?php if ($edit_data): ?>
                            <input type="hidden" name="id_bandara" value="<?= $edit_data['id_bandara'] ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="nama_bandara" class="form-label fw-semibold">Nama Bandara</label>
                            <input type="text" class="form-control bg-light" id="nama_bandara" name="nama_bandara" value="<?= htmlspecialchars($edit_data['nama_bandara'] ?? '') ?>" placeholder="Contoh: Soekarno-Hatta International" required>
                        </div>

                        <div class="mb-3">
                            <label for="kode_bandara" class="form-label fw-semibold">Kode Bandara (IATA)</label>
                            <input type="text" class="form-control bg-light text-uppercase" id="kode_bandara" name="kode_bandara" value="<?= htmlspecialchars($edit_data['kode_bandara'] ?? '') ?>" placeholder="Contoh: CGK" maxlength="5" required>
                        </div>

                        <div class="mb-3">
                            <label for="kota" class="form-label fw-semibold">Kota</label>
                            <input type="text" class="form-control bg-light" id="kota" name="kota" value="<?= htmlspecialchars($edit_data['kota'] ?? '') ?>" placeholder="Contoh: Jakarta" required>
                        </div>

                        <div class="mb-3">
                            <label for="wilayah" class="form-label fw-semibold">Wilayah</label>
                            <select class="form-select bg-light" id="wilayah" name="wilayah" required>
                                <option value="Barat" <?= (isset($edit_data['wilayah']) && $edit_data['wilayah'] === 'Barat') ? 'selected' : '' ?>>Barat (Jakarta, Palembang, Lampung, dsb)</option>
                                <option value="Timur" <?= (isset($edit_data['wilayah']) && $edit_data['wilayah'] === 'Timur') ? 'selected' : '' ?>>Timur (Makassar, Jayapura, Ambon, dsb)</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="status" class="form-label fw-semibold">Status</label>
                            <select class="form-select bg-light" id="status" name="status" required>
                                <option value="aktif" <?= (isset($edit_data['status']) && $edit_data['status'] === 'aktif') ? 'selected' : '' ?>>Aktif</option>
                                <option value="nonaktif" <?= (isset($edit_data['status']) && $edit_data['status'] === 'nonaktif') ? 'selected' : '' ?>>Nonaktif</option>
                            </select>
                        </div>

                        <button type="submit" name="<?= $edit_data ? 'edit_bandara' : 'add_bandara' ?>" class="btn <?= $edit_data ? 'btn-warning' : 'btn-primary' ?> w-100 fw-bold">
                            <?= $edit_data ? '<i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan' : '<i class="fa-solid fa-plus me-1"></i> Tambah Bandara' ?>
                        </button>
                        
                        <?php if ($edit_data): ?>
                            <a href="index.php" class="btn btn-outline-secondary w-100 fw-bold mt-2">Batal</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- List Card -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3 border-bottom pb-2">
                        <h5 class="fw-bold text-secondary mb-0">Daftar Bandara</h5>
                        <form action="" method="GET" class="d-flex" style="max-width: 300px;">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control bg-light" placeholder="Cari nama, kota, kode..." value="<?= htmlspecialchars($search) ?>">
                                <button class="btn btn-primary" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Kode</th>
                                    <th>Nama Bandara</th>
                                    <th>Kota</th>
                                    <th>Wilayah</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($airports)): ?>
                                    <?php foreach ($airports as $ap): ?>
                                        <tr>
                                            <td><?= $ap['id_bandara'] ?></td>
                                            <td class="font-monospace fw-bold text-primary"><?= htmlspecialchars($ap['kode_bandara']) ?></td>
                                            <td><?= htmlspecialchars($ap['nama_bandara']) ?></td>
                                            <td><?= htmlspecialchars($ap['kota']) ?></td>
                                            <td>
                                                <span class="badge <?= $ap['wilayah'] === 'Barat' ? 'bg-warning-subtle text-warning' : 'bg-info-subtle text-info' ?> px-2.5 py-1 rounded-pill">
                                                    <?= htmlspecialchars($ap['wilayah']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?= $ap['status'] === 'aktif' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?> px-2 py-1 rounded-pill">
                                                    <?= htmlspecialchars($ap['status']) ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a href="index.php?action=edit&id=<?= $ap['id_bandara'] ?>" class="btn btn-outline-warning btn-sm me-1" title="Edit">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                                <a href="index.php?action=delete&id=<?= $ap['id_bandara'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus bandara ini?');" title="Hapus">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">Bandara tidak ditemukan.</td>
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
