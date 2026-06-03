<?php
// Handle Download Action before headers are sent!
$backup_dir = __DIR__ . '/../../backup';
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0777, true);
}

if (isset($_GET['action']) && $_GET['action'] === 'download' && isset($_GET['file'])) {
    $file = basename($_GET['file']);
    $filepath = $backup_dir . '/' . $file;
    if (file_exists($filepath) && pathinfo($filepath, PATHINFO_EXTENSION) === 'sql') {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    }
}

$page_title = "Backup Database";
require_once '../../includes/header.php';

// Authentication & Role Check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error'] = "Akses ditolak! Anda harus masuk sebagai Admin.";
    header("Location: ../../auth/login.php");
    exit();
}

$success_msg = null;
$error_msg = null;

// Handle Backup Execution
if (isset($_POST['run_backup'])) {
    $filename = 'jelajahudara_' . date('Ymd_His') . '.sql';
    $filepath = $backup_dir . '/' . $filename;
    
    // Command: mysqldump -u root jelajahudara > path
    // Under windows/laragon/xampp, calling mysqldump works if in PATH.
    // Let's write the command:
    $command = "mysqldump -u root jelajahudara > " . escapeshellarg($filepath) . " 2>&1";
    
    // Run Command
    $output = [];
    $retval = -1;
    exec($command, $output, $retval);
    
    if ($retval === 0 && file_exists($filepath) && filesize($filepath) > 0) {
        $success_msg = "Database berhasil dibackup dengan nama: <strong>" . $filename . "</strong>";
    } else {
        $error_msg = "Gagal membackup database! Kode error: " . $retval . ". ";
        if (!empty($output)) {
            $error_msg .= "Detail: " . htmlspecialchars(implode(' ', $output));
        }
        $error_msg .= "<br><em>Saran: Pastikan utilitas 'mysqldump' berada dalam System PATH OS Anda.</em>";
    }
}

// Handle Delete Backup File
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['file'])) {
    $file = basename($_GET['file']);
    $filepath = $backup_dir . '/' . $file;
    if (file_exists($filepath) && pathinfo($filepath, PATHINFO_EXTENSION) === 'sql') {
        if (unlink($filepath)) {
            $success_msg = "File backup <strong>" . $file . "</strong> berhasil dihapus.";
        } else {
            $error_msg = "Gagal menghapus file backup.";
        }
    }
}

// Scan backup folder for existing files
$backup_files = [];
if (is_dir($backup_dir)) {
    $files = scandir($backup_dir);
    foreach ($files as $f) {
        if (pathinfo($f, PATHINFO_EXTENSION) === 'sql') {
            $backup_files[] = [
                'name' => $f,
                'size' => filesize($backup_dir . '/' . $f),
                'date' => filemtime($backup_dir . '/' . $f)
            ];
        }
    }
    // Sort files by date descending
    usort($backup_files, function($a, $b) {
        return $b['date'] - $a['date'];
    });
}
?>

<div class="admin-wrapper">
    <!-- Include Sidebar -->
    <?php require_once '../../includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <main class="admin-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1"><i class="fa-solid fa-database text-primary me-2"></i> Backup Database</h3>
                <p class="text-muted mb-0">Melakukan pencadangan (backup) skema & data database JelajahUdara kapan saja secara instan.</p>
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
            <!-- Execution Card -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white text-center">
                    <div class="bg-primary bg-opacity-10 text-primary p-4 rounded-circle mx-auto mb-3" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-download-database fs-1" style="font-size: 2.5rem;"></i>
                    </div>
                    <h5 class="fw-bold text-secondary mb-2">Cadangkan Sekarang</h5>
                    <p class="text-muted small mb-4">Akan mengeksekusi perintah <code>mysqldump</code> untuk menyimpan data saat ini ke folder <code>backup/</code>.</p>
                    
                    <form action="" method="POST">
                        <button type="submit" name="run_backup" class="btn btn-primary w-100 py-3 fw-bold">
                            <i class="fa-solid fa-circle-down me-2"></i> Backup Sekarang
                        </button>
                    </form>
                </div>
            </div>

            <!-- List Card -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <h5 class="fw-bold text-secondary mb-3 border-bottom pb-2">Daftar File Cadangan (SQL)</h5>
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama File Backup</th>
                                    <th>Ukuran File</th>
                                    <th>Tanggal Pembuatan</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($backup_files)): ?>
                                    <?php foreach ($backup_files as $bf): ?>
                                        <tr>
                                            <td class="font-monospace fw-semibold"><i class="fa-solid fa-file-code text-primary me-2"></i><?= htmlspecialchars($bf['name']) ?></td>
                                            <td>
                                                <span class="badge bg-light text-dark border">
                                                    <?= round($bf['size'] / 1024, 2) ?> KB
                                                </span>
                                            </td>
                                            <td><i class="fa-solid fa-calendar-days text-muted me-1"></i><?= date('d M Y, H:i', $bf['date']) ?></td>
                                            <td class="text-center">
                                                <a href="index.php?action=download&file=<?= urlencode($bf['name']) ?>" class="btn btn-outline-primary btn-sm me-1" title="Download">
                                                    <i class="fa-solid fa-download"></i> Download
                                                </a>
                                                <a href="index.php?action=delete&file=<?= urlencode($bf['name']) ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus file backup ini?');" title="Hapus">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">Belum ada file backup database.</td>
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
