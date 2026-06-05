<?php
if (!defined('BASE_URL')) {
    define('BASE_URL', '/JelajahUdara-main/');
}
?>

<?php if (!isset($is_admin_layout) || !$is_admin_layout): ?>
<footer class="no-print mt-auto">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <a href="<?= BASE_URL ?>" class="navbar-brand mb-3 d-inline-block text-white font-weight-extrabold" style="font-size:1.4rem;">
                    <i class="fa-solid fa-plane-departure text-info"></i> Jelajah<span>Udara</span>
                </a>
                <p class="text-white mt-2">
                    Solusi pemesanan tiket pesawat modern yang siap menemani setiap langkah penjelajahan keindahan Nusantara dengan cepat, mudah, dan aman.
                </p>
                <div class="d-flex gap-3 mt-3">
                    <a href="#" class="text-white bg-secondary p-2 rounded-circle" style="width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center;"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="text-white bg-secondary p-2 rounded-circle" style="width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center;"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" class="text-white bg-secondary p-2 rounded-circle" style="width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center;"><i class="fa-brands fa-twitter"></i></a>
                </div>
            </div>
            <div class="col-lg-2 col-md-6 col-6">
                <h6 class="text-white mb-3">Tautan Cepat</h6>
                <ul class="list-unstyled d-flex flex-column gap-2">
                    <li><a href="<?= BASE_URL ?>">Home</a></li>
                    <li><a href="<?= BASE_URL ?>customer/cari_tiket.php">Cari Tiket</a></li>
                    <li><a href="<?= BASE_URL ?>#promo">Promo Spesial</a></li>
                    <li><a href="<?= BASE_URL ?>#tentang-kami">Tentang Kami</a></li>
                </ul>
            </div>
            <div class="col-lg-2 col-md-6 col-6">
                <h6 class="text-white mb-3">Maskapai Populer</h6>
                <ul class="list-unstyled d-flex flex-column gap-2">
                    <li><a href="#">Garuda Indonesia</a></li>
                    <li><a href="#">Batik Air</a></li>
                    <li><a href="#">Lion Air</a></li>
                    <li><a href="#">Citilink</a></li>
                </ul>
            </div>
            <div class="col-lg-4 col-md-6">
                <h6 class="text-white mb-3">Hubungi Kami</h6>
                <ul class="list-unstyled d-flex flex-column gap-2 text-white">
                    <li><i class="fa-solid fa-location-dot text-primary me-2"></i> Jl. Penerbangan No. 45, Jakarta, Indonesia</li>
                    <li><i class="fa-solid fa-phone text-primary me-2"></i> +62 21 1234 5678</li>
                    <li><i class="fa-solid fa-envelope text-primary me-2"></i> support@jelajahudara.com</li>
                </ul>
            </div>
        </div>
        <hr class="border-secondary my-4">
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start">
                <p class="mb-0 text-white">&copy; <?= date('Y') ?> JelajahUdara. Hak Cipta Dilindungi Undang-Undang.</p>
            </div>
            <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
                <a href="#" class="text-white me-3">Syarat & Ketentuan</a>
                <a href="#" class="text-white">Kebijakan Privasi</a>
            </div>
        </div>
    </div>
</footer>
<?php endif; ?>

    <!-- Bootstrap Bundle with Popper JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
