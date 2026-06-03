<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = filter_input(INPUT_POST, 'nama_lengkap', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    if (empty($nama_lengkap) || empty($email) || empty($password) || empty($konfirmasi_password)) {
        $_SESSION['error'] = "Semua kolom harus diisi!";
        header("Location: ../auth/register.php");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Format email tidak valid!";
        header("Location: ../auth/register.php");
        exit();
    }

    if ($password !== $konfirmasi_password) {
        $_SESSION['error'] = "Konfirmasi kata sandi tidak cocok!";
        header("Location: ../auth/register.php");
        exit();
    }

    if (strlen($password) < 6) {
        $_SESSION['error'] = "Kata sandi minimal harus 6 karakter!";
        header("Location: ../auth/register.php");
        exit();
    }

    $database = new Database();
    $db = $database->getConnection();

    try {
        // Check if email already exists
        $check_query = "SELECT id_user FROM users WHERE email = :email LIMIT 1";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':email', $email);
        $check_stmt->execute();

        if ($check_stmt->rowCount() > 0) {
            $_SESSION['error'] = "Email sudah terdaftar! Gunakan email lain.";
            header("Location: ../auth/register.php");
            exit();
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $role = 'customer';

        // Insert new user
        $insert_query = "INSERT INTO users (nama, nama_lengkap, email, password, role) VALUES (:nama, :nama_lengkap, :email, :password, :role)";
        $insert_stmt = $db->prepare($insert_query);
        $insert_stmt->bindParam(':nama', $nama_lengkap);
        $insert_stmt->bindParam(':nama_lengkap', $nama_lengkap);
        $insert_stmt->bindParam(':email', $email);
        $insert_stmt->bindParam(':password', $hashed_password);
        $insert_stmt->bindParam(':role', $role);

        if ($insert_stmt->execute()) {
            $_SESSION['success'] = "Pendaftaran berhasil! Silakan masuk dengan akun baru Anda.";
            header("Location: ../auth/login.php");
            exit();
        } else {
            $_SESSION['error'] = "Gagal mendaftarkan akun. Silakan coba lagi.";
            header("Location: ../auth/register.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Terjadi kesalahan sistem: " . $e->getMessage();
        header("Location: ../auth/register.php");
        exit();
    }
} else {
    header("Location: ../auth/register.php");
    exit();
}
?>
