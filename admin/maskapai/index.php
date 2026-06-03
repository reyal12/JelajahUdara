<?php
$page_title = "Kelola Maskapai";
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
    // Add Maskapai (Calls Stored Procedure `tambah_maskapai`)
    if (isset($_POST['add_maskapai'])) {
        $nama = filter_input(INPUT_POST, 'nama_maskapai', FILTER_SANITIZE_SPECIAL_CHARS);
        $kode = filter_input(INPUT_POST, 'kode_maskapai', FILTER_SANITIZE_SPECIAL_CHARS);
        $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS);

        if (empty($nama) || empty($kode) || empty($status)) {
            $error_msg = "Semua kolom harus diisi!";
        } else {
            try {
                // Call Stored Procedure
                $query = "CALL tambah_maskapai(:nama, :kode, :status)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':nama', $nama);
                $stmt->bindParam(':kode', $kode);
                $stmt->bindParam(':status', $status);
                
                if ($stmt->execute()) {
                    $success_msg = "Maskapai baru berhasil ditambahkan via Stored Procedure!";
                } else {
                    $error_msg = "Gagal menambahkan maskapai.";
                }
            } catch (PDOException $e) {
                $error_msg = "Kesalahan database: " . $e->getMessage();
            }
        }
    }

    // Edit Maskapai
    if (isset($_POST['edit_maskapai'])) {
        $id = intval($_POST['id_maskapai']);
        $nama = filter_input(INPUT_POST, 'nama_maskapai', FILTER_SANITIZE_SPECIAL_CHARS);
        $kode = filter_input(INPUT_POST, 'kode_maskapai', FILTER_SANITIZE_SPECIAL_CHARS);
        $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS);

        if (empty($nama) || empty($kode) || empty($status)) {
            $error_msg = "Semua kolom harus diisi!";
        } else {
            try {
                $query = "UPDATE maskapai SET nama_maskapai = :nama, kode_maskapai = :kode, status = :status WHERE id_maskapai = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':nama', $nama);
                $stmt->bindParam(':kode', $kode);
                $stmt->bindParam(':status', $status);
                $stmt->bindParam(':id', $id);

                if ($stmt->execute()) {
                    $success_msg = "Maskapai berhasil diperbarui!";
                } else {
                    $error_msg = "Gagal memperbarui maskapai.";
                }
            } catch (PDOException $e) {
                $error_msg = "Kesalahan database: " . $e->getMessage();
            }
        }
    }
}

// Delete Maskapai (Calls Stored Procedure `hapus_maskapai`)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    try {
        // Call Stored Procedure
        $query = "CALL hapus_maskapai(:id)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            $success_msg = "Maskapai berhasil dihapus via Stored Procedure!";
        } else {
            $error_msg = "Gagal menghapus maskapai.";
        }
    } catch (PDOException $e) {
        $error_msg = "Kesalahan database atau dependensi data: " . $e->getMessage();
    }
}

// Fetch single Maskapai for Edit mode
$edit_data = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    try {
        $query = "SELECT * FROM maskapai WHERE id_maskapai = :id LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {}
}

// Search & List Maskapai
$search = isset($_GET['search']) ? filter_input(INPUT_GET, 'search', FILTER_SANITIZE_SPECIAL_CHARS) : '';
$airlines = [];
try {
    if (!empty($search)) {
        $query = "SELECT * FROM maskapai WHERE nama_maskapai LIKE :search OR kode_maskapai LIKE :search ORDER BY nama_maskapai ASC";
        $stmt = $db->prepare($query);
        $search_param = "%" . $search . "%";
        $stmt->bindParam(':search', $search_param);
    } else {
        $query = "SELECT * FROM maskapai ORDER BY nama_maskapai ASC";
        $stmt = $db->prepare($query);
    }
    $stmt->execute();
    $airlines = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
?>

<div class="admin-wrapper">
    <!-- Include Sidebar -->
    <?php require_once '../../includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <main class="admin-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Kelola Maskapai</h3>
                <p class="text-muted mb-0">Manajemen armada maskapai JelajahUdara.</p>
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
                        <?= $edit_data ? '<i class="fa-solid fa-pen-to-square text-warning me-2"></i> Edit Maskapai' : '<i class="fa-solid fa-square-plus text-primary me-2"></i> Tambah Maskapai' ?>
                    </h5>
                    
                    <form action="index.php" method="POST">
                        <?php if ($edit_data): ?>
                            <input type="hidden" name="id_maskapai" value="<?= $edit_data['id_maskapai'] ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="nama_maskapai" class="form-label fw-semibold">Nama Maskapai</label>
                            <input type="text" class="form-control bg-light" id="nama_maskapai" name="nama_maskapai" value="<?= htmlspecialchars($edit_data['nama_maskapai'] ?? '') ?>" placeholder="Contoh: Garuda Indonesia" required>
                        </div>

                        <div class="mb-3">
                            <label for="kode_maskapai" class="form-label fw-semibold">Kode Maskapai (IATA)</label>
                            <input type="text" class="form-control bg-light text-uppercase" id="kode_maskapai" name="kode_maskapai" value="<?= htmlspecialchars($edit_data['kode_maskapai'] ?? '') ?>" placeholder="Contoh: GA" maxlength="5" required>
                        </div>

                        <div class="mb-4">
                            <label for="status" class="form-label fw-semibold">Status</label>
                            <select class="form-select bg-light" id="status" name="status" required>
                                <option value="aktif" <?= (isset($edit_data['status']) && $edit_data['status'] === 'aktif') ? 'selected' : '' ?>>Aktif</option>
                                <option value="nonaktif" <?= (isset($edit_data['status']) && $edit_data['status'] === 'nonaktif') ? 'selected' : '' ?>>Nonaktif</option>
                            </select>
                        </div>

                        <button type="submit" name="<?= $edit_data ? 'edit_maskapai' : 'add_maskapai' ?>" class="btn <?= $edit_data ? 'btn-warning' : 'btn-primary' ?> w-100 fw-bold">
                            <?= $edit_data ? '<i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan' : '<i class="fa-solid fa-plus me-1"></i> Tambah Maskapai' ?>
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
                        <h5 class="fw-bold text-secondary mb-0">Daftar Maskapai</h5>
                        <form action="" method="GET" class="d-flex" style="max-width: 300px;">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control bg-light" placeholder="Cari nama/kode..." value="<?= htmlspecialchars($search) ?>">
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
                                    <th>Nama Maskapai</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($airlines)): ?>
                                    <?php foreach ($airlines as $al): ?>
                                        <tr>
                                            <td><?= $al['id_maskapai'] ?></td>
                                            <td class="font-monospace fw-bold text-primary"><?= htmlspecialchars($al['kode_maskapai']) ?></td>
                                            <td><?= htmlspecialchars($al['nama_maskapai']) ?></td>
                                            <td>
                                                <span class="badge <?= $al['status'] === 'aktif' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?> px-2.5 py-1.5 rounded-pill text-capitalize">
                                                    <?= $al['status'] ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a href="index.php?action=edit&id=<?= $al['id_maskapai'] ?>" class="btn btn-outline-warning btn-sm me-1" title="Edit">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                                <a href="index.php?action=delete&id=<?= $al['id_maskapai'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus maskapai ini?');" title="Hapus">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Maskapai tidak ditemukan.</td>
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
