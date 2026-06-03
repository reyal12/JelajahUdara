<?php
$page_title = "Masuk ke Akun Anda";
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card custom-card overflow-hidden">
                <div class="card-header bg-gradient-primary text-white text-center py-4 border-0">
                    <h3 class="fw-bold mb-1"><i class="fa-solid fa-plane-departure me-2"></i> JelajahUdara</h3>
                    <p class="mb-0 text-white-50">Selamat datang kembali! Silakan masuk.</p>
                </div>
                <div class="card-body p-4 p-md-5">
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i>
                            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fa-solid fa-circle-check me-2"></i>
                            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="<?= BASE_URL ?>process/login_process.php" method="POST">
                        <div class="mb-4">
                            <label for="email" class="form-label fw-semibold">Alamat Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-envelope text-muted"></i></span>
                                <input type="email" name="email" id="email" class="form-control bg-light border-start-0 ps-0" placeholder="nama@email.com" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <label for="password" class="form-label fw-semibold mb-0">Kata Sandi</label>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                                <input type="password" name="password" id="password" class="form-control bg-light border-start-0 ps-0" placeholder="Masukkan kata sandi Anda" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fs-6 fw-bold mt-2">
                            Masuk <i class="fa-solid fa-arrow-right-to-bracket ms-2"></i>
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <p class="text-muted mb-0">Belum punya akun? <a href="<?= BASE_URL ?>auth/register.php" class="text-primary fw-bold text-decoration-none">Daftar Sekarang</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
