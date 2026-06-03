<?php
$page_title = "Cetak E-Ticket";
require_once '../includes/header.php';
require_once '../config/database.php';

// Authentication Check
if (!isset($_SESSION['user_id'])) {
    die("<div class='container my-5 text-center'><div class='alert alert-danger'>Silakan login terlebih dahulu!</div></div>");
}

$id_pemesanan = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_pemesanan <= 0) {
    die("<div class='container my-5 text-center'><div class='alert alert-danger'>Pemesanan tidak valid!</div></div>");
}

$database = new Database();
$db = $database->getConnection();

$ticket = null;
try {
    $query = "SELECT p.*, f.tanggal_berangkat, f.tanggal_tiba, m.nama_maskapai, m.kode_maskapai,
                     b_asal.nama_bandara AS nama_asal, b_asal.kota AS kota_asal, b_asal.kode_bandara AS kode_asal,
                     b_tuj.nama_bandara AS nama_tujuan, b_tuj.kota AS kota_tujuan, b_tuj.kode_bandara AS kode_tujuan,
                     u.nama_lengkap, u.email
              FROM pemesanan p
              JOIN users u ON p.id_user = u.id_user
              JOIN penerbangan f ON p.id_penerbangan = f.id_penerbangan
              JOIN maskapai m ON f.id_maskapai = m.id_maskapai
              JOIN bandara b_asal ON f.bandara_asal = b_asal.id_bandara
              JOIN bandara b_tuj ON f.bandara_tujuan = b_tuj.id_bandara
              WHERE p.id_pemesanan = :id AND (p.id_user = :user_id OR :role = 'admin') LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id_pemesanan);
    $stmt->bindValue(':user_id', $_SESSION['user_id']);
    $stmt->bindValue(':role', $_SESSION['user_role']);
    $stmt->execute();
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

if (!$ticket) {
    die("<div class='container my-5 text-center'><div class='alert alert-danger'>Tiket tidak ditemukan atau Anda tidak memiliki akses!</div></div>");
}

if ($ticket['status_pemesanan'] !== 'Berhasil') {
    die("<div class='container my-5 text-center'><div class='alert alert-warning'>Tiket belum lunas / belum aktif. Silakan lakukan pembayaran terlebih dahulu.</div></div>");
}
?>

<div class="container my-5">
    <!-- Action bar (hidden in print) -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print bg-white p-3 rounded-3 shadow-sm border">
        <a href="riwayat.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i> Kembali</a>
        <button onclick="window.print()" class="btn btn-primary"><i class="fa-solid fa-print me-2"></i> Cetak E-Ticket / PDF</button>
    </div>

    <!-- Ticket layout -->
    <div class="card ticket-print-container border shadow-sm p-4 p-md-5 bg-white">
        <!-- Header -->
        <div class="row align-items-center mb-4 pb-4 border-bottom">
            <div class="col-md-6 col-12 text-center text-md-start mb-3 mb-md-0">
                <h3 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-plane-departure me-2"></i> JelajahUdara</h3>
                <small class="text-muted">E-Ticket Penerbangan Resmi</small>
            </div>
            <div class="col-md-6 col-12 text-center text-md-end">
                <span class="text-muted small d-block">KODE BOOKING (PNR)</span>
                <h3 class="font-monospace fw-bold text-dark mb-0"><?= htmlspecialchars($ticket['kode_booking']) ?></h3>
                <span class="badge bg-success-subtle text-success mt-1 px-3 py-1.5 rounded-pill fw-bold">E-TICKET AKTIF / LUNAS</span>
            </div>
        </div>

        <!-- Flight info -->
        <div class="bg-light p-4 rounded-4 mb-4 border">
            <div class="row align-items-center g-4">
                <div class="col-md-4">
                    <span class="text-muted small d-block mb-1">MASKAPAI</span>
                    <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-plane me-2 text-primary"></i> <?= htmlspecialchars($ticket['nama_maskapai']) ?></h5>
                    <span class="text-muted font-monospace"><?= htmlspecialchars($ticket['kode_maskapai']) ?>-<?= $ticket['id_penerbangan'] ?></span>
                </div>
                <div class="col-md-8">
                    <div class="row align-items-center">
                        <div class="col-5">
                            <span class="text-muted small d-block">KEBERANGKATAN</span>
                            <h4 class="fw-bold text-primary mb-0"><?= htmlspecialchars($ticket['kode_asal']) ?></h4>
                            <span class="small fw-semibold d-block text-dark"><?= htmlspecialchars($ticket['kota_asal']) ?></span>
                            <small class="text-muted"><?= date('d M Y, H:i', strtotime($ticket['tanggal_berangkat'])) ?></small>
                        </div>
                        <div class="col-2 text-center">
                            <i class="fa-solid fa-circle-arrow-right text-primary fs-3"></i>
                        </div>
                        <div class="col-5 text-end">
                            <span class="text-muted small d-block">KEDATANGAN</span>
                            <h4 class="fw-bold text-primary mb-0"><?= htmlspecialchars($ticket['kode_tujuan']) ?></h4>
                            <span class="small fw-semibold d-block text-dark"><?= htmlspecialchars($ticket['kota_tujuan']) ?></span>
                            <small class="text-muted"><?= date('d M Y, H:i', strtotime($ticket['tanggal_tiba'])) ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Passenger list & billing details -->
        <div class="row g-4 mb-4">
            <div class="col-md-7">
                <h5 class="fw-bold text-secondary mb-3"><i class="fa-solid fa-users text-primary me-2"></i> Detail Penumpang</h5>
                <div class="table-responsive">
                    <table class="table border">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Penumpang</th>
                                <th>Jenis Tiket</th>
                                <th>Bagasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($i = 1; $i <= intval($ticket['jumlah_penumpang']); $i++): ?>
                                <tr>
                                    <td><?= $i ?></td>
                                    <td><?= htmlspecialchars($ticket['nama_lengkap']) ?> (Pax #<?= $i ?>)</td>
                                    <td>Dewasa</td>
                                    <td>20 KG</td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="col-md-5">
                <h5 class="fw-bold text-secondary mb-3"><i class="fa-solid fa-circle-info text-primary me-2"></i> Detail Pembayaran</h5>
                <div class="card p-3 border">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Status Pembayaran</span>
                        <span class="text-success fw-bold">LUNAS</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Jumlah Penumpang</span>
                        <span><?= $ticket['jumlah_penumpang'] ?> Orang</span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-dark">Total Pembayaran</span>
                        <h4 class="fw-bold text-success mb-0">Rp <?= number_format($ticket['total_harga'], 0, ',', '.') ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Important Notes -->
        <div class="border-top pt-4 mt-4">
            <h6 class="fw-bold text-danger mb-2"><i class="fa-solid fa-triangle-exclamation me-2"></i> CATATAN PENTING:</h6>
            <ol class="small text-muted ps-3 mb-0 d-flex flex-column gap-1">
                <li>Harap tiba di bandara selambat-lambatnya 2 (dua) jam sebelum jadwal keberangkatan.</li>
                <li>Tunjukkan E-Ticket ini beserta kartu identitas resmi (KTP/Paspor) yang berlaku pada saat Check-in.</li>
                <li>Check-in ditutup 45 menit sebelum waktu keberangkatan domestik.</li>
                <li>Dilarang membawa barang-barang berbahaya (explosive, flammable, toxic) di dalam bagasi kabin maupun bagasi tercatat.</li>
            </ol>
        </div>

        <!-- Mock barcode footer -->
        <div class="text-center mt-5 pt-3 border-top">
            <div class="d-inline-block p-2 bg-light border mb-2">
                <i class="fa-solid fa-barcode fs-1 text-dark" style="font-size: 5rem !important;"></i>
            </div>
            <p class="small text-muted mb-0 font-monospace">JU-PNR-<?= htmlspecialchars($ticket['kode_booking']) ?>-<?= $ticket['id_pemesanan'] ?></p>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
