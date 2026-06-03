<?php
session_start();
require_once '../config/database.php';

// Authentication check
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Silakan login terlebih dahulu untuk memesan tiket!";
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_penerbangan = isset($_POST['id_penerbangan']) ? intval($_POST['id_penerbangan']) : 0;
    $jumlah_penumpang = isset($_POST['jumlah_penumpang']) ? intval($_POST['jumlah_penumpang']) : 1;

    if ($id_penerbangan <= 0 || $jumlah_penumpang <= 0) {
        $_SESSION['booking_error'] = true;
        $_SESSION['booking_msg'] = "Transaksi Gagal: Data penerbangan tidak valid.";
        header("Location: ../customer/cari_tiket.php");
        exit();
    }

    $database = new Database();
    $db = $database->getConnection();

    // Generate unique booking code
    $kode_booking = "JU" . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

    // Start database transaction
    $db->beginTransaction();

    try {
        // 1. Fetch flight with lock (FOR UPDATE) to prevent race conditions
        $query_flight = "SELECT harga, kursi_tersedia FROM penerbangan WHERE id_penerbangan = :id FOR UPDATE";
        $stmt_flight = $db->prepare($query_flight);
        $stmt_flight->bindParam(':id', $id_penerbangan);
        $stmt_flight->execute();
        $flight = $stmt_flight->fetch(PDO::FETCH_ASSOC);

        if (!$flight) {
            throw new Exception("Penerbangan tidak ditemukan.");
        }

        $kursi_tersedia = intval($flight['kursi_tersedia']);
        if ($kursi_tersedia < $jumlah_penumpang) {
            throw new Exception("Jumlah kursi yang diminta tidak tersedia. Sisa kursi saat ini: " . $kursi_tersedia);
        }

        // 2. Fetch discounted price using the custom SQL function hitung_diskon()
        $harga_satuan = $flight['harga'] * 0.90; // default 10% discount in PHP
        try {
            $q_disc = "SELECT hitung_diskon(:harga) AS diskon";
            $s_disc = $db->prepare($q_disc);
            $s_disc->bindValue(':harga', $flight['harga']);
            $s_disc->execute();
            $r_disc = $s_disc->fetch(PDO::FETCH_ASSOC);
            if ($r_disc) {
                $harga_satuan = $r_disc['diskon'];
            }
        } catch (PDOException $e_disc) {
            // Fallback to PHP computation if SQL function fails
        }

        $total_harga = $harga_satuan * $jumlah_penumpang;

        // 3. Save booking data (pemesanan)
        $query_booking = "INSERT INTO pemesanan (kode_booking, id_user, id_penerbangan, jumlah_penumpang, total_harga, status_pemesanan) 
                          VALUES (:kode_booking, :id_user, :id_penerbangan, :jumlah_penumpang, :total_harga, 'Pending')";
        $stmt_booking = $db->prepare($query_booking);
        $stmt_booking->bindParam(':kode_booking', $kode_booking);
        $stmt_booking->bindValue(':id_user', $_SESSION['user_id']);
        $stmt_booking->bindParam(':id_penerbangan', $id_penerbangan);
        $stmt_booking->bindParam(':jumlah_penumpang', $jumlah_penumpang);
        $stmt_booking->bindParam(':total_harga', $total_harga);
        $stmt_booking->execute();

        $id_pemesanan = $db->lastInsertId();

        // 4. Update and reduce flight seats
        $query_update = "UPDATE penerbangan SET kursi_tersedia = kursi_tersedia - :jumlah WHERE id_penerbangan = :id";
        $stmt_update = $db->prepare($query_update);
        $stmt_update->bindParam(':jumlah', $jumlah_penumpang);
        $stmt_update->bindParam(':id', $id_penerbangan);
        $stmt_update->execute();

        // 5. Commit Transaction
        $db->commit();

        $_SESSION['booking_success'] = true;
        $_SESSION['booking_msg'] = "Transaksi Berhasil! Silakan pilih metode pembayaran untuk menyelesaikan tiket Anda.";
        header("Location: ../customer/pembayaran.php?id=" . $id_pemesanan);
        exit();

    } catch (Exception $e) {
        // Rollback Transaction if any operation fails
        $db->rollBack();
        
        $_SESSION['booking_error'] = true;
        $_SESSION['booking_msg'] = "Transaksi Gagal: " . $e->getMessage();
        header("Location: ../customer/pemesanan.php?id=" . $id_penerbangan);
        exit();
    }
} else {
    header("Location: ../customer/cari_tiket.php");
    exit();
}
?>
