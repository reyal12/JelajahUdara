<?php
$page_title = "Laporan Database";
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

$inner_join_results = [];
$left_join_results = [];
$union_results = [];
$union_all_results = [];

try {
    // 1. INNER JOIN: User Name, Booking Code, and Booking Status
    $query_inner = "SELECT u.nama_lengkap, p.kode_booking, p.status_pemesanan, p.total_harga 
                    FROM pemesanan p
                    INNER JOIN users u ON p.id_user = u.id_user
                    ORDER BY p.tanggal_pemesanan DESC";
    $stmt_inner = $db->query($query_inner);
    $inner_join_results = $stmt_inner->fetchAll(PDO::FETCH_ASSOC);

    // 2. LEFT JOIN: All Users and their Booking counts & sums (Using COUNT, SUM, CONCAT)
    $query_left = "SELECT u.id_user, u.nama_lengkap, u.email, 
                          COUNT(p.id_pemesanan) AS jumlah_booking, 
                          CONCAT('Rp ', FORMAT(IFNULL(SUM(p.total_harga), 0), 0, 'id_ID')) AS formatted_total_belanja
                   FROM users u
                   LEFT JOIN pemesanan p ON u.id_user = p.id_user
                   GROUP BY u.id_user
                   ORDER BY jumlah_booking DESC";
    $stmt_left = $db->query($query_left);
    $left_join_results = $stmt_left->fetchAll(PDO::FETCH_ASSOC);

    // 3. UNION: Get unique list of flight cities (Origin + Destination)
    $query_union = "SELECT kota_asal AS nama_kota, 'Kota Asal' AS kategori FROM vw_jadwal_penerbangan
                    UNION
                    SELECT kota_tujuan AS nama_kota, 'Kota Tujuan' AS kategori FROM vw_jadwal_penerbangan
                    ORDER BY nama_kota ASC";
    $stmt_union = $db->query($query_union);
    $union_results = $stmt_union->fetchAll(PDO::FETCH_ASSOC);

    // 4. UNION ALL: Combined view of flight lists from Barat and Timur views (Western & Eastern fragmentations)
    $query_union_all = "SELECT 'Barat' AS asal_wilayah, nama_maskapai, kota_asal, kota_tujuan, harga 
                        FROM penerbangan_barat
                        UNION ALL
                        SELECT 'Timur' AS asal_wilayah, nama_maskapai, kota_asal, kota_tujuan, harga 
                        FROM penerbangan_timur
                        ORDER BY harga ASC";
    $stmt_union_all = $db->query($query_union_all);
    $union_all_results = $stmt_union_all->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $report_error = "Kesalahan dalam menjalankan kueri laporan: " . $e->getMessage();
}
?>

<div class="admin-wrapper">
    <!-- Include Sidebar -->
    <?php require_once '../../includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <main class="admin-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Modul Laporan Database</h3>
                <p class="text-muted mb-0">Laporan terintegrasi menggunakan implementasi kueri SQL JOIN, UNION, dan fungsi bawaan database.</p>
            </div>
        </div>

        <?php if (isset($report_error)): ?>
            <div class="alert alert-danger p-3 rounded-3 mb-4 shadow-sm" role="alert">
                <i class="fa-solid fa-circle-xmark me-2"></i> <?= $report_error ?>
            </div>
        <?php endif; ?>

        <!-- Nav tabs for report categorization -->
        <ul class="nav nav-pills mb-4 bg-white p-2 rounded-3 shadow-sm" id="reportTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold" id="inner-tab" data-bs-toggle="tab" data-bs-target="#inner-pane" type="button" role="tab" aria-controls="inner-pane" aria-selected="true">
                    <i class="fa-solid fa-link me-1"></i> INNER JOIN (Pemesanan Aktif)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold" id="left-tab" data-bs-toggle="tab" data-bs-target="#left-pane" type="button" role="tab" aria-controls="left-pane" aria-selected="false">
                    <i class="fa-solid fa-arrows-split-up-and-left me-1"></i> LEFT JOIN (Daftar Customer)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold" id="union-tab" data-bs-toggle="tab" data-bs-target="#union-pane" type="button" role="tab" aria-controls="union-pane" aria-selected="false">
                    <i class="fa-solid fa-compress me-1"></i> SQL UNION (Kota Bandara)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold" id="unionall-tab" data-bs-toggle="tab" data-bs-target="#unionall-pane" type="button" role="tab" aria-controls="unionall-pane" aria-selected="false">
                    <i class="fa-solid fa-circle-nodes me-1"></i> UNION ALL (Gabungan Penerbangan)
                </button>
            </li>
        </ul>

        <div class="tab-content" id="reportTabsContent">
            <!-- 1. INNER JOIN Pane -->
            <div class="tab-pane fade show active" id="inner-pane" role="tabpanel" aria-labelledby="inner-tab">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <div class="mb-3">
                        <h5 class="fw-bold text-secondary">Laporan INNER JOIN</h5>
                        <p class="text-muted small">Menghubungkan data pemesanan dengan pengguna yang melakukan transaksi secara eksplisit.</p>
                        <pre class="bg-light p-3 border rounded text-dark font-monospace" style="font-size:0.85rem;">
SELECT u.nama_lengkap, p.kode_booking, p.status_pemesanan, p.total_harga 
FROM pemesanan p
INNER JOIN users u ON p.id_user = u.id_user;</pre>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Customer</th>
                                    <th>Kode Booking</th>
                                    <th>Total Tagihan</th>
                                    <th>Status Pemesanan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($inner_join_results)): ?>
                                    <?php $no = 1; foreach ($inner_join_results as $row): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><strong><?= htmlspecialchars($row['nama_lengkap']) ?></strong></td>
                                            <td class="font-monospace fw-bold"><?= htmlspecialchars($row['kode_booking']) ?></td>
                                            <td class="fw-bold text-success">Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></td>
                                            <td>
                                                <span class="badge <?= $row['status_pemesanan'] === 'Berhasil' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' ?> px-2.5 py-1 rounded-pill">
                                                    <?= htmlspecialchars($row['status_pemesanan']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Belum ada data pemesanan.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 2. LEFT JOIN Pane -->
            <div class="tab-pane fade" id="left-pane" role="tabpanel" aria-labelledby="left-tab">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <div class="mb-3">
                        <h5 class="fw-bold text-secondary">Laporan LEFT JOIN (Daftar Seluruh Pengguna)</h5>
                        <p class="text-muted small">Menampilkan semua data pengguna sistem beserta riwayat pembelian mereka (meskipun belum pernah memesan ticket).</p>
                        <pre class="bg-light p-3 border rounded text-dark font-monospace" style="font-size:0.85rem;">
SELECT u.nama_lengkap, u.email, COUNT(p.id_pemesanan) AS jumlah_booking, 
       CONCAT('Rp ', FORMAT(IFNULL(SUM(p.total_harga), 0), 0, 'id_ID')) AS formatted_total_belanja
FROM users u
LEFT JOIN pemesanan p ON u.id_user = p.id_user
GROUP BY u.id_user;</pre>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Pengguna</th>
                                    <th>Email</th>
                                    <th>Jumlah Pemesanan</th>
                                    <th>Total Transaksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($left_join_results)): ?>
                                    <?php $no = 1; foreach ($left_join_results as $row): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><strong><?= htmlspecialchars($row['nama_lengkap']) ?></strong></td>
                                            <td><?= htmlspecialchars($row['email']) ?></td>
                                            <td>
                                                <span class="badge bg-light text-dark border px-2.5 py-1">
                                                    <?= $row['jumlah_booking'] ?> Transaksi
                                                </span>
                                            </td>
                                            <td class="fw-bold text-primary"><?= htmlspecialchars($row['formatted_total_belanja']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Belum ada data user.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 3. SQL UNION Pane -->
            <div class="tab-pane fade" id="union-pane" role="tabpanel" aria-labelledby="union-tab">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <div class="mb-3">
                        <h5 class="fw-bold text-secondary">Laporan SQL UNION (Daftar Kota Bandara Terdaftar)</h5>
                        <p class="text-muted small">Menggabungkan kota asal dan kota tujuan dari seluruh jadwal penerbangan untuk menghasilkan daftar kota unik tanpa duplikat.</p>
                        <pre class="bg-light p-3 border rounded text-dark font-monospace" style="font-size:0.85rem;">
SELECT kota_asal AS nama_kota, 'Kota Asal' AS kategori FROM vw_jadwal_penerbangan
UNION
SELECT kota_tujuan AS nama_kota, 'Kota Tujuan' AS kategori FROM vw_jadwal_penerbangan;</pre>
                    </div>

                    <div class="table-responsive" style="max-width: 500px;">
                        <table class="table table-sm table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Kota Unik</th>
                                    <th>Kategori Contoh</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($union_results)): ?>
                                    <?php $no = 1; foreach ($union_results as $row): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($row['nama_kota']) ?></td>
                                            <td><span class="badge bg-light text-secondary"><?= htmlspecialchars($row['kategori']) ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">Belum ada rute kota.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 4. UNION ALL Pane -->
            <div class="tab-pane fade" id="unionall-pane" role="tabpanel" aria-labelledby="unionall-tab">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <div class="mb-3">
                        <h5 class="fw-bold text-secondary">Laporan SQL UNION ALL (Gabungan Fragmentasi Wilayah)</h5>
                        <p class="text-muted small">Menggabungkan seluruh record penerbangan dari fragmentasi Barat dan fragmentasi Timur menjadi satu laporan gabungan utuh dengan mempertahankan duplikasi jika ada.</p>
                        <pre class="bg-light p-3 border rounded text-dark font-monospace" style="font-size:0.85rem;">
SELECT 'Barat' AS asal_wilayah, nama_maskapai, kota_asal, kota_tujuan, harga FROM penerbangan_barat
UNION ALL
SELECT 'Timur' AS asal_wilayah, nama_maskapai, kota_asal, kota_tujuan, harga FROM penerbangan_timur;</pre>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Asal Wilayah</th>
                                    <th>Maskapai</th>
                                    <th>Rute</th>
                                    <th>Harga Penerbangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($union_all_results)): ?>
                                    <?php $no = 1; foreach ($union_all_results as $row): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td>
                                                <span class="badge <?= $row['asal_wilayah'] === 'Barat' ? 'bg-warning-subtle text-warning' : 'bg-info-subtle text-info' ?> px-2.5 py-1.5 rounded-pill">
                                                    <?= htmlspecialchars($row['asal_wilayah']) ?>
                                                </span>
                                            </td>
                                            <td><strong><?= htmlspecialchars($row['nama_maskapai']) ?></strong></td>
                                            <td><?= htmlspecialchars($row['kota_asal']) ?> &rarr; <?= htmlspecialchars($row['kota_tujuan']) ?></td>
                                            <td class="fw-bold text-success">Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Belum ada data penerbangan wilayah Barat/Timur.</td>
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
