<?php
$page_title = "Daftar Akun Baru";
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card custom-card overflow-hidden">
                <div class="card-header bg-gradient-primary text-white text-center py-4 border-0">
                    <h3 class="fw-bold mb-1"><i class="fa-solid fa-plane-departure me-2"></i> Daftar JelajahUdara</h3>
                    <p class="mb-0 text-white-50">Temukan petualangan Anda bersama kami.</p>
                </div>
                <div class="card-body p-4 p-md-5">

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i>
                            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="<?= BASE_URL ?>process/register_process.php" method="POST">
                        <div class="mb-3">
                            <label for="nama_lengkap" class="form-label fw-semibold">Nama Lengkap</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-user text-muted"></i></span>
                                <input type="text" name="nama_lengkap" id="nama_lengkap" class="form-control bg-light border-start-0 ps-0" placeholder="Masukkan nama lengkap Anda" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Alamat Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-envelope text-muted"></i></span>
                                <input type="email" name="email" id="email" class="form-control bg-light border-start-0 ps-0" placeholder="nama@email.com" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">Kata Sandi</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                                <input type="password" name="password" id="password" class="form-control bg-light border-start-0 ps-0" placeholder="Minimal 6 karakter" required minlength="6">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="konfirmasi_password" class="form-label fw-semibold">Konfirmasi Kata Sandi</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock-open text-muted"></i></span>
                                <input type="password" name="konfirmasi_password" id="konfirmasi_password" class="form-control bg-light border-start-0 ps-0" placeholder="Ulangi kata sandi Anda" required minlength="6">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fs-6 fw-bold mt-2">
                            Daftar Sekarang <i class="fa-solid fa-user-plus ms-2"></i>
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <p class="text-muted mb-0">Sudah memiliki akun? <a href="<?= BASE_URL ?>auth/login.php" class="text-primary fw-bold text-decoration-none">Masuk di sini</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
