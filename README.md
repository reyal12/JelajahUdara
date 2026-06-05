# JelajahUdara (Proyek Uap)

JelajahUdara adalah sistem pemesanan tiket pesawat berbasis web yang memungkinkan pengguna mencari penerbangan, memesan tiket, melakukan pembayaran, dan mengelola riwayat perjalanan. Sistem ini juga dirancang untuk mengimplementasikan berbagai konsep database seperti View, Join, Set Operation, Transaction, Function, Stored Procedure, Trigger, Fragmentasi Database, Backup Database, dan Task Scheduler.
<img width="2539" height="1263" alt="image" src="https://github.com/user-attachments/assets/0b49b1aa-190b-4b69-8a00-1f0311ad36a8" />

# Detail Konsep
## 1. Database Views, SQL Joins & Set Operations
<img width="1045" height="171" alt="image" src="https://github.com/user-attachments/assets/63725efd-4967-418a-b7a1-abce960f7041" />

View `vw_jadwal_penerbangan` digunakan sebagai sumber data utama yang menggabungkan tabel `penerbangan`, `maskapai`, dan `bandara`. View ini diakses di halaman pencarian tiket dan laporan agar query tetap bersih dan konsisten.

`customer/cari_tiket.php`

```php
// Mencari tiket menggunakan view vw_jadwal_penerbangan
$query_search = "SELECT *, hitung_diskon(harga) AS harga_diskon 
                 FROM vw_jadwal_penerbangan 
                 WHERE status_penerbangan = 'aktif' 
                 AND kursi_tersedia > 0 
                 ORDER BY tanggal_berangkat ASC";
```

Pada halaman laporan, terdapat empat query berbeda yang mendemonstrasikan JOIN dan Set Operation:

`admin/laporan/index.php`

**INNER JOIN** — menampilkan data pemesanan beserta nama pengguna yang melakukan booking:
<img width="1587" height="411" alt="image" src="https://github.com/user-attachments/assets/db03cc6e-1094-4354-8da9-3516e5f28105" />

```php
$query_inner = "SELECT u.nama_lengkap, p.kode_booking, p.status_pemesanan, p.total_harga 
                FROM pemesanan p
                INNER JOIN users u ON p.id_user = u.id_user
                ORDER BY p.tanggal_pesan DESC";
```

**LEFT JOIN** — menampilkan seluruh pengguna terdaftar beserta jumlah booking dan total belanja mereka, termasuk pengguna yang belum pernah memesan:
<img width="1584" height="318" alt="image" src="https://github.com/user-attachments/assets/3c456050-65f6-4dac-9d2f-360d26db4196" />


```php
$query_left = "SELECT u.id_user, u.nama_lengkap, u.email, 
                      COUNT(p.id_pemesanan) AS jumlah_booking, 
                      CONCAT('Rp ', FORMAT(IFNULL(SUM(p.total_harga), 0), 0, 'id_ID')) AS formatted_total_belanja
               FROM users u
               LEFT JOIN pemesanan p ON u.id_user = p.id_user
               GROUP BY u.id_user
               ORDER BY jumlah_booking DESC";
```

**UNION** — menggabungkan daftar kota asal dan kota tujuan dari view menjadi satu daftar unik tanpa duplikasi:
<img width="491" height="290" alt="image" src="https://github.com/user-attachments/assets/c819054f-5c6e-4e97-af5c-ae3793084c62" />


```php
$query_union = "SELECT kota_asal AS nama_kota, 'Kota Asal' AS kategori FROM vw_jadwal_penerbangan
                UNION
                SELECT kota_tujuan AS nama_kota, 'Kota Tujuan' AS kategori FROM vw_jadwal_penerbangan
                ORDER BY nama_kota ASC";
```

**UNION ALL** — menggabungkan seluruh data penerbangan dari dua fragmentasi wilayah (barat dan timur) termasuk duplikat:
<img width="1583" height="143" alt="image" src="https://github.com/user-attachments/assets/1950e2b3-b6f7-4a37-b5dd-8f5415de9bac" />

```php
$query_union_all = "SELECT 'Barat' AS asal_wilayah, nama_maskapai, kota_asal, kota_tujuan, harga 
                    FROM penerbangan_barat
                    UNION ALL
                    SELECT 'Timur' AS asal_wilayah, nama_maskapai, kota_asal, kota_tujuan, harga 
                    FROM penerbangan_timur
                    ORDER BY harga ASC";
```

---

## 2. Transaction

Proses pemesanan tiket menggunakan transaksi database yang atomik. Baris penerbangan dikunci dengan `FOR UPDATE` untuk mencegah race condition ketika banyak pengguna memesan kursi yang sama secara bersamaan. Jika salah satu langkah gagal, seluruh operasi di-rollback sehingga data tetap konsisten.

`process/booking_process.php`

```php
$db->beginTransaction();

try {
    // 1. Kunci baris penerbangan agar tidak bisa diubah transaksi lain (FOR UPDATE)
    $query_flight = "SELECT harga, kursi_tersedia FROM penerbangan 
                     WHERE id_penerbangan = :id FOR UPDATE";
    $stmt_flight = $db->prepare($query_flight);
    $stmt_flight->bindParam(':id', $id_penerbangan);
    $stmt_flight->execute();
    $flight = $stmt_flight->fetch(PDO::FETCH_ASSOC);

    // 2. Validasi ketersediaan kursi
    if ($flight['kursi_tersedia'] < $jumlah_penumpang) {
        throw new Exception("Jumlah kursi yang diminta tidak tersedia.");
    }

    // 3. Simpan data pemesanan
    $query_booking = "INSERT INTO pemesanan 
                      (kode_booking, id_user, id_penerbangan, jumlah_tiket, total_harga, status_pemesanan) 
                      VALUES (:kode_booking, :id_user, :id_penerbangan, :jumlah_tiket, :total_harga, 'pending')";
    $stmt_booking = $db->prepare($query_booking);
    $stmt_booking->execute();

    // 4. Kurangi kursi tersedia
    $query_update = "UPDATE penerbangan SET kursi_tersedia = kursi_tersedia - :jumlah 
                     WHERE id_penerbangan = :id";
    $stmt_update = $db->prepare($query_update);
    $stmt_update->execute();

    // 5. Commit jika semua langkah berhasil
    $db->commit();

} catch (Exception $e) {
    // Rollback jika ada langkah yang gagal
    $db->rollBack();
}
```

---

## 3. Deadlock

Simulasi deadlock dilakukan dengan menjalankan dua transaksi secara bersamaan menggunakan `Promise.allSettled()` di sisi JavaScript, di mana masing-masing transaksi mengunci baris yang sama namun dengan urutan terbalik sehingga terjadi saling tunggu (circular wait).

MySQL mendeteksi kondisi ini dan secara otomatis melakukan rollback pada salah satu transaksi (error code `1213`). Sistem kemudian mencoba ulang transaksi yang di-rollback hingga tiga kali dengan jeda acak sebelum menyatakan gagal.

`admin/process_deadlock.php`

```php
$db->exec("SET innodb_lock_wait_timeout = 10");

$max_retries = 3;
$retry_count = 0;

while ($retry_count < $max_retries && !$success) {
    try {
        $db->beginTransaction();

        if ($tx === 1) {
            // Transaksi 1: kunci id1 dulu, lalu id2
            $db->exec("UPDATE penerbangan SET harga = harga + 1 WHERE id_penerbangan = $id1");
            sleep(2);
            $db->exec("UPDATE penerbangan SET harga = harga - 1 WHERE id_penerbangan = $id2");
        } else {
            // Transaksi 2: kunci id2 dulu, lalu id1 — urutan terbalik, memicu deadlock
            $db->exec("UPDATE penerbangan SET harga = harga + 1 WHERE id_penerbangan = $id2");
            sleep(2);
            $db->exec("UPDATE penerbangan SET harga = harga - 1 WHERE id_penerbangan = $id1");
        }

        $db->commit();
        $success = true;

    } catch (PDOException $e) {
        $db->rollBack();

        // Error 1213 = Deadlock detected by MySQL
        if ($e->errorInfo[1] == 1213) {
            $retry_count++;
            // Jeda acak 0.1–0.5 detik sebelum retry agar tidak bertabrakan di waktu yang sama
            usleep(rand(100000, 500000));
        }
    }
}
```

---

## 4. Function, Trigger, Fragmentasi, dan Backup
<img width="797" height="477" alt="image" src="https://github.com/user-attachments/assets/530e8676-acd3-4941-983d-6683e24e8fd5" />
<img width="775" height="226" alt="image" src="https://github.com/user-attachments/assets/4a497747-db07-492c-bfe5-d9551a3018d9" />


### Function

`hitung_diskon(p_harga)` adalah SQL function yang menghitung harga tiket setelah diskon 10%. Function ini dipanggil langsung dalam query SQL saat pencarian tiket dan proses pemesanan.

`process/booking_process.php`

```php
// Memanggil SQL function hitung_diskon() untuk mendapatkan harga setelah diskon
$q_disc = "SELECT hitung_diskon(:harga) AS diskon";
$s_disc = $db->prepare($q_disc);
$s_disc->bindValue(':harga', $flight['harga']);
$s_disc->execute();
$r_disc = $s_disc->fetch(PDO::FETCH_ASSOC);

if ($r_disc) {
    $harga_satuan = $r_disc['diskon'];
}

$total_harga = $harga_satuan * $jumlah_penumpang;
```

### Stored Procedure

Dua stored procedure digunakan pada halaman kelola penerbangan:

`admin/penerbangan/index.php`

```php
// Menampilkan seluruh daftar penerbangan via stored procedure
$query = "CALL tampil_penerbangan()";
$stmt = $db->prepare($query);
$stmt->execute();
$flights = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Memperbarui harga penerbangan via stored procedure
$query = "CALL update_harga_penerbangan(:id, :harga)";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id_penerbangan);
$stmt->bindParam(':harga', $harga_baru);
$stmt->execute();
```

### Fragmentasi
<img width="1584" height="120" alt="image" src="https://github.com/user-attachments/assets/a18ec0a7-226c-47dc-ae81-cba4a559f0ea" />
<img width="1578" height="113" alt="image" src="https://github.com/user-attachments/assets/34de9f74-1735-41f7-b118-15907aba92ef" />

Data penerbangan difragmentasi secara horizontal ke dalam dua view berdasarkan wilayah asal: `penerbangan_barat` (Jakarta, Lampung, Palembang) dan `penerbangan_timur` (Makassar, Ambon, Jayapura). Masing-masing view diakses pada halaman terpisah.

`admin/penerbangan/barat.php`

```php
$query = "SELECT * FROM penerbangan_barat ORDER BY tanggal_berangkat ASC";
$stmt = $db->prepare($query);
$stmt->execute();
$flights = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

`admin/penerbangan/timur.php`

```php
$query = "SELECT * FROM penerbangan_timur ORDER BY tanggal_berangkat ASC";
$stmt = $db->prepare($query);
$stmt->execute();
$flights = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

### Backup Otomatis

Backup otomatis berjalan satu kali sehari menggunakan mekanisme pseudo-cron. Setiap kali halaman web diakses, sistem memeriksa apakah backup untuk hari itu sudah ada. Jika belum, `mysqldump` dijalankan dan hasilnya disimpan di direktori `hasilbackup/` dengan nama file bertanda waktu.

`includes/auto_backup.php`

```php
function run_auto_backup()
{
    $backup_dir = __DIR__ . '/../hasilbackup';
    $log_file   = $backup_dir . '/last_backup.txt';
    $today      = date('Y-m-d');

    if (!is_dir($backup_dir)) {
        @mkdir($backup_dir, 0777, true);
    }

    $last_backup = file_exists($log_file) ? trim(file_get_contents($log_file)) : '';

    if ($last_backup !== $today) {
        $filename = 'autobackup_' . date('Ymd_His') . '.sql';
        $filepath = $backup_dir . '/' . $filename;

        $mysqldump_path = 'E:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysqldump.exe';
        if (!file_exists($mysqldump_path)) {
            $mysqldump_path = 'mysqldump'; // Fallback ke PATH sistem
        }

        $command = '"' . $mysqldump_path . '" -u root jelajahudara > ' 
                   . escapeshellarg($filepath) . ' 2>&1';

        exec($command, $output, $retval);

        if ($retval === 0 && file_exists($filepath) && filesize($filepath) > 0) {
            file_put_contents($log_file, $today);
        }
    }
}

@run_auto_backup();
```

### Backup Otomatis via Task Scheduler

Selain pseudo-cron, sistem menyediakan `backup_harian.bat` yang didaftarkan ke Windows Task Scheduler agar berjalan otomatis setiap hari pukul 23:00 tanpa campur tangan admin.

`backup_harian.bat`

```bat
@echo off
cd /d "%~dp0"
set FILENAME=backup_harian_%date:~10,4%%date:~4,2%%date:~7,2%.sql
mysqldump -u root jelajahudara > hasilbackup\%FILENAME%
echo Backup completed successfully: %FILENAME%
```

### Backup Manual

Admin juga dapat memicu backup kapan saja melalui tombol di halaman `admin/backup/` tanpa harus menunggu jadwal otomatis.
