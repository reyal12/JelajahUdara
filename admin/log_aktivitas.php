<?php
$page_title = "Log Aktivitas Pemesanan";
require_once '../includes/header.php';

// Authentication & Role Check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error'] = "Akses ditolak! Anda harus masuk sebagai Admin.";
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

$logs = [];
try {
    // Read activity logs
    $query = "SELECT * FROM log_pemesanan ORDER BY waktu_log DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
?>

<div class="admin-wrapper">
    <!-- Include Sidebar -->
    <?php require_once '../includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <main class="admin-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i> Log Aktivitas Pemesanan</h3>
                <p class="text-muted mb-0">Memantau riwayat audit log otomatis yang dipicu oleh database trigger <code>AFTER INSERT ON pemesanan</code>.</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID Log</th>
                            <th>ID Pemesanan</th>
                            <th>Aktivitas Log</th>
                            <th>Waktu Log Kejadian</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($logs)): ?>
                            <?php foreach ($logs as $l): ?>
                                <tr>
                                    <td><span class="badge bg-secondary">#<?= $l['id_log'] ?></span></td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary">Pemesanan ID: <?= $l['id_pemesanan'] ?></span>
                                    </td>
                                    <td><strong class="text-dark"><?= htmlspecialchars($l['aktivitas']) ?></strong></td>
                                    <td class="text-muted"><i class="fa-solid fa-clock me-1"></i> <?= date('d M Y, H:i:s', strtotime($l['waktu_log'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">Belum ada catatan log aktivitas pemesanan di database.</td>
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
require_once '../includes/footer.php'; 
?>
