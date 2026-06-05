<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Ensure user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak.']);
    exit();
}

// LEPASKAN LOCK SESSION!
// PHP secara default mengunci file session. Jika tidak dilepas, request ke-2 akan menunggu 
// request ke-1 selesai, sehingga tidak akan pernah terjadi eksekusi bersamaan (deadlock).
session_write_close();

$tx = isset($_GET['tx']) ? intval($_GET['tx']) : 0;

if ($tx !== 1 && $tx !== 2) {
    echo json_encode(['status' => 'error', 'message' => 'Parameter tx tidak valid.']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Set timeout shorter so we don't wait forever if something hangs
$db->exec("SET innodb_lock_wait_timeout = 10");

try {
    // Get two distinct flight IDs dynamically
    $stmt = $db->query("SELECT id_penerbangan FROM penerbangan LIMIT 2");
    $flights = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($flights) < 2) {
        echo json_encode(['status' => 'error', 'message' => 'Dibutuhkan minimal 2 data penerbangan untuk simulasi ini.']);
        exit();
    }

    $id1 = $flights[0];
    $id2 = $flights[1];

    $max_retries = 3;
    $retry_count = 0;
    $success = false;
    $is_deadlocked_before = false;

    while ($retry_count < $max_retries && !$success) {
        try {
            $db->beginTransaction();

            if ($tx === 1) {
                $db->exec("UPDATE penerbangan SET harga = harga + 1 WHERE id_penerbangan = $id1");
                sleep(2);
                $db->exec("UPDATE penerbangan SET harga = harga - 1 WHERE id_penerbangan = $id2");
            } else if ($tx === 2) {
                $db->exec("UPDATE penerbangan SET harga = harga + 1 WHERE id_penerbangan = $id2");
                sleep(2);
                $db->exec("UPDATE penerbangan SET harga = harga - 1 WHERE id_penerbangan = $id1");
            }

            $db->commit();
            $success = true;

            $message = "Transaksi $tx berhasil dieksekusi.";
            if ($is_deadlocked_before) {
                $message = "Transaksi $tx terkena Deadlock, namun otomatis berhasil diatasi (Retry ke-$retry_count) dan tersimpan dengan aman!";
                $status = 'warning'; // Beri tahu UI bahwa ini pulih dari deadlock
            } else {
                $status = 'success';
            }

            echo json_encode([
                'status' => $status, 
                'tx' => $tx,
                'message' => $message
            ]);

        } catch (PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            
            $error_code = $e->errorInfo[1] ?? 0;
            if ($error_code == 1213) {
                $is_deadlocked_before = true;
                $retry_count++;
                
                if ($retry_count >= $max_retries) {
                    echo json_encode([
                        'status' => 'deadlock', 
                        'tx' => $tx,
                        'message' => "Terjadi Deadlock pada Transaksi $tx! Gagal dipulihkan setelah $max_retries kali percobaan."
                    ]);
                    exit();
                }
                
                // Jeda acak (0.1 - 0.5 detik) sebelum mencoba lagi agar tidak bertabrakan di waktu yang sama persis
                usleep(rand(100000, 500000));
            } else {
                echo json_encode([
                    'status' => 'error', 
                    'tx' => $tx,
                    'message' => "Error database: " . $e->getMessage()
                ]);
                exit();
            }
        }
    }
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode([
        'status' => 'error', 
        'tx' => isset($tx) ? $tx : 0,
        'message' => "Sistem Error: " . $e->getMessage()
    ]);
}
?>
