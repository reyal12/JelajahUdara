<?php
$page_title = "Edit Profil Saya";
require_once '../includes/header.php';
require_once '../includes/navbar.php';
require_once '../config/database.php';

// Authentication Check
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Silakan login terlebih dahulu!";
    header("Location: ../auth/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$user_id = $_SESSION['user_id'];

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $nama_lengkap = filter_input(INPUT_POST, 'nama_lengkap', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    if (empty($nama_lengkap) || empty($email)) {
        $error_msg = "Nama lengkap dan email tidak boleh kosong!";
    } else {
        try {
            // Check if email already taken by someone else
            $check_email = "SELECT id_user FROM users WHERE email = :email AND id_user != :id LIMIT 1";
            $stmt_check = $db->prepare($check_email);
            $stmt_check->bindParam(':email', $email);
            $stmt_check->bindParam(':id', $user_id);
            $stmt_check->execute();

            if ($stmt_check->rowCount() > 0) {
                $error_msg = "Email sudah digunakan oleh pengguna lain!";
            } else {
                if (!empty($password)) {
                    if (strlen($password) < 6) {
                        $error_msg = "Kata sandi baru minimal harus 6 karakter!";
                    } else {
                        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                        $query_update = "UPDATE users SET nama_lengkap = :nama, email = :email, password = :password WHERE id_user = :id";
                        $stmt_update = $db->prepare($query_update);
                        $stmt_update->bindParam(':password', $hashed_password);
                    }
                } else {
                    $query_update = "UPDATE users SET nama_lengkap = :nama, email = :email WHERE id_user = :id";
                    $stmt_update = $db->prepare($query_update);
                }

                if (!isset($error_msg)) {
                    $stmt_update->bindParam(':nama', $nama_lengkap);
                    $stmt_update->bindParam(':email', $email);
                    $stmt_update->bindParam(':id', $user_id);
                    
                    if ($stmt_update->execute()) {
                        $_SESSION['user_name'] = $nama_lengkap;
                        $_SESSION['user_email'] = $email;
                        $success_msg = "Profil berhasil diperbarui!";
                    } else {
                        $error_msg = "Gagal memperbarui profil. Silakan coba lagi.";
                    }
                }
            }
        } catch (PDOException $e) {
            $error_msg = "Terjadi kesalahan sistem: " . $e->getMessage();
        }
    }
}

// Fetch current user details
$user = null;
try {
    $query = "SELECT * FROM users WHERE id_user = :id LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
?>

<main class="container my-5">
    <div class="row">
        <!-- Customer navigation menu -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="text-center py-3 border-bottom mb-3">
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle mx-auto mb-3" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-user fs-2"></i>
                    </div>
                    <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($_SESSION['user_name']) ?></h6>
                    <small class="text-muted"><?= htmlspecialchars($_SESSION['user_email']) ?></small>
                </div>
                <div class="list-group list-group-flush">
                    <a href="dashboard.php" class="list-group-item list-group-item-action border-0 py-2.5 rounded-3"><i class="fa-solid fa-gauge me-3 text-secondary"></i> Dashboard</a>
                    <a href="cari_tiket.php" class="list-group-item list-group-item-action border-0 py-2.5 rounded-3"><i class="fa-solid fa-plane-departure me-3 text-secondary"></i> Cari Tiket</a>
                    <a href="riwayat.php" class="list-group-item list-group-item-action border-0 py-2.5 rounded-3"><i class="fa-solid fa-receipt me-3 text-secondary"></i> Pemesanan Saya</a>
                    <a href="profil.php" class="list-group-item list-group-item-action border-0 py-2.5 rounded-3 active"><i class="fa-solid fa-user-gear me-3"></i> Profil Saya</a>
                    <a href="../auth/logout.php" class="list-group-item list-group-item-action border-0 py-2.5 rounded-3 text-danger"><i class="fa-solid fa-right-from-bracket me-3"></i> Logout</a>
                </div>
            </div>
        </div>

        <!-- Profile Edit Form Content -->
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <h4 class="fw-bold mb-4 text-secondary"><i class="fa-solid fa-user-gear text-primary me-2"></i> Pengaturan Profil</h4>

                <?php if (isset($error_msg)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i> <?= $error_msg ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($success_msg)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-circle-check me-2"></i> <?= $success_msg ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form action="" method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="nama_lengkap" class="form-label fw-semibold">Nama Lengkap</label>
                            <input type="text" class="form-control bg-light" id="nama_lengkap" name="nama_lengkap" value="<?= htmlspecialchars($user['nama_lengkap'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label fw-semibold">Alamat Email</label>
                            <input type="email" class="form-control bg-light" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                        </div>
                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-secondary border-bottom pb-2">Ubah Kata Sandi (Opsional)</h6>
                        </div>
                        <div class="col-md-6">
                            <label for="password" class="form-label fw-semibold">Kata Sandi Baru</label>
                            <input type="password" class="form-control bg-light" id="password" name="password" placeholder="Kosongkan jika tidak ingin diubah" minlength="6">
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" name="update_profile" class="btn btn-primary px-4 py-2.5 fw-bold">
                                <i class="fa-solid fa-floppy-disk me-2"></i> Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
