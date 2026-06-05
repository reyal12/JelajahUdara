<?php
// auto_backup.php
// Script ini berjalan di background secara otomatis setiap kali halaman web diakses.
// Tujuannya adalah membuat backup 1x sehari (Pseudo-Cron).

function run_auto_backup()
{
    $backup_dir = __DIR__ . '/../hasilbackup';
    $log_file = $backup_dir . '/last_backup.txt';
    $today = date('Y-m-d');

    // Pastikan folder backup ada
    if (!is_dir($backup_dir)) {
        @mkdir($backup_dir, 0777, true);
    }

    // Cek apakah hari ini sudah di-backup
    $last_backup = '';
    if (file_exists($log_file)) {
        $last_backup = trim(file_get_contents($log_file));
    }

    // Jika belum dibackup hari ini, jalankan proses backup
    if ($last_backup !== $today) {
        $filename = 'autobackup_' . date('Ymd_His') . '.sql';
        $filepath = $backup_dir . '/' . $filename;

        // Menentukan path absolut mysqldump Laragon
        $mysqldump_path = 'E:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysqldump.exe';
        if (!file_exists($mysqldump_path)) {
            $mysqldump_path = 'mysqldump'; // Fallback
        }

        $command = '"' . $mysqldump_path . '" -u root jelajahudara > ' . escapeshellarg($filepath) . ' 2>&1';

        $output = [];
        $retval = -1;
        exec($command, $output, $retval);

        // Jika berhasil terbuat dan ukuran file > 0, catat tanggal hari ini
        if ($retval === 0 && file_exists($filepath) && filesize($filepath) > 0) {
            file_put_contents($log_file, $today);
        }
    }
}

// Panggil fungsi secara aman (suppress errors agar tidak mengganggu UI web)
@run_auto_backup();
?>