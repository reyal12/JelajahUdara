<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('BASE_URL')) {
    define('BASE_URL', '/JelajahUdara-main/');
}

// Jalankan Web-Based Auto Backup (Pseudo-Cron)
require_once __DIR__ . '/auto_backup.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' - JelajahUdara' : 'JelajahUdara - Jelajahi Indonesia Bersama Kami' ?></title>
    
    <!-- Meta tags for SEO -->
    <meta name="description" content="JelajahUdara - Aplikasi pemesanan tiket pesawat modern, responsif, dan terpercaya untuk seluruh rute penerbangan di Indonesia.">
    <meta name="keywords" content="tiket pesawat, booking tiket, jelajah udara, penerbangan indonesia, garuda indonesia, batik air, lion air">
    <meta name="author" content="JelajahUdara Team">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
</head>
<body>
