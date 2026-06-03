<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Semua kolom harus diisi!";
        header("Location: ../auth/login.php");
        exit();
    }

    $database = new Database();
    $db = $database->getConnection();

    try {
        $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Save user session
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['user_name'] = $user['nama'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: ../admin/dashboard.php");
            } else {
                header("Location: ../customer/dashboard.php");
            }
            exit();
        } else {
            $_SESSION['error'] = "Email atau kata sandi Anda salah!";
            header("Location: ../auth/login.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Terjadi kesalahan sistem: " . $e->getMessage();
        header("Location: ../auth/login.php");
        exit();
    }
} else {
    header("Location: ../auth/login.php");
    exit();
}
?>
