<?php
class Database {
    private $host = "localhost";
    private $db_name = "jelajahudara";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
            
            // Run schema initialization dynamically to ensure views, procedures, trigger, and functions exist
            $this->initializeDatabase();
        } catch(PDOException $exception) {
            die("Connection error: " . $exception->getMessage());
        }
        return $this->conn;
    }

    private function initializeDatabase() {
        try {
            // 1. Recreate vw_jadwal_penerbangan to include more columns safely
            $q_view = "CREATE OR REPLACE VIEW vw_jadwal_penerbangan AS
                SELECT 
                  p.id_penerbangan,
                  p.id_maskapai,
                  m.nama_maskapai,
                  m.kode_maskapai,
                  p.asal_bandara,
                  b_asal.nama_bandara AS nama_bandara_asal,
                  b_asal.kode_bandara AS kode_bandara_asal,
                  b_asal.kota AS kota_asal,
                  p.tujuan_bandara,
                  b_tuj.nama_bandara AS nama_bandara_tujuan,
                  b_tuj.kode_bandara AS kode_bandara_tujuan,
                  b_tuj.kota AS kota_tujuan,
                  p.tanggal_berangkat,
                  p.jam_berangkat,
                  p.jam_tiba,
                  p.harga,
                  p.kursi_tersedia,
                  p.status_penerbangan
                FROM penerbangan p
                JOIN maskapai m ON p.id_maskapai = m.id_maskapai
                JOIN bandara b_asal ON p.asal_bandara = b_asal.id_bandara
                JOIN bandara b_tuj ON p.tujuan_bandara = b_tuj.id_bandara";
            $this->conn->exec($q_view);

            // 2. Recreate penerbangan_barat view
            $q_barat = "CREATE OR REPLACE VIEW penerbangan_barat AS
                SELECT * FROM vw_jadwal_penerbangan
                WHERE kota_asal IN ('Jakarta', 'Lampung', 'Palembang')";
            $this->conn->exec($q_barat);

            // 3. Recreate penerbangan_timur view
            $q_timur = "CREATE OR REPLACE VIEW penerbangan_timur AS
                SELECT * FROM vw_jadwal_penerbangan
                WHERE kota_asal IN ('Makassar', 'Ambon', 'Jayapura')";
            $this->conn->exec($q_timur);
        } catch (Exception $e) {
            // Log or ignore view issues if table dependencies are not met yet
        }

        // 4. Create custom function hitung_diskon if not exists
        try {
            $this->conn->exec("DROP FUNCTION IF EXISTS hitung_diskon");
            $q_func = "CREATE FUNCTION hitung_diskon(harga DECIMAL(10,2))
                RETURNS DECIMAL(10,2)
                DETERMINISTIC
                BEGIN
                  DECLARE harga_diskon DECIMAL(10,2);
                  SET harga_diskon = harga * 0.90;
                  RETURN harga_diskon;
                END";
            $this->conn->exec($q_func);
        } catch (Exception $e) {
            // Ignore if lack of super privileges on localhost
        }

        // 5. Create Stored Procedures
        try {
            $this->conn->exec("DROP PROCEDURE IF EXISTS tambah_maskapai");
            $this->conn->exec("CREATE PROCEDURE tambah_maskapai(IN p_nama VARCHAR(100), IN p_kode VARCHAR(10), IN p_status VARCHAR(20))
                BEGIN
                  INSERT INTO maskapai (nama_maskapai, kode_maskapai, status) VALUES (p_nama, p_kode, p_status);
                END");
        } catch (Exception $e) {}

        try {
            $this->conn->exec("DROP PROCEDURE IF EXISTS hapus_maskapai");
            $this->conn->exec("CREATE PROCEDURE hapus_maskapai(IN p_id INT)
                BEGIN
                  DELETE FROM maskapai WHERE id_maskapai = p_id;
                END");
        } catch (Exception $e) {}

        try {
            $this->conn->exec("DROP PROCEDURE IF EXISTS tampil_penerbangan");
            $this->conn->exec("CREATE PROCEDURE tampil_penerbangan()
                BEGIN
                  SELECT * FROM vw_jadwal_penerbangan ORDER BY tanggal_berangkat ASC;
                END");
        } catch (Exception $e) {}

        try {
            $this->conn->exec("DROP PROCEDURE IF EXISTS update_harga_penerbangan");
            $this->conn->exec("CREATE PROCEDURE update_harga_penerbangan(IN p_id INT, IN p_harga_baru DECIMAL(10,2))
                BEGIN
                  UPDATE penerbangan SET harga = p_harga_baru WHERE id_penerbangan = p_id;
                END");
        } catch (Exception $e) {}

        // 6. Create trigger after_pemesanan_insert if not exists
        try {
            $this->conn->exec("DROP TRIGGER IF EXISTS after_pemesanan_insert");
            $q_trig = "CREATE TRIGGER after_pemesanan_insert
                AFTER INSERT ON pemesanan
                FOR EACH ROW
                BEGIN
                  INSERT INTO log_pemesanan (id_pemesanan, aktivitas, waktu_log)
                  VALUES (NEW.id_pemesanan, CONCAT('Pemesanan baru dibuat. Kode: ', NEW.kode_booking, ', User ID: ', NEW.id_user, ', Tiket: ', NEW.jumlah_tiket), NOW());
                END";
            $this->conn->exec($q_trig);
        } catch (Exception $e) {}
    }
}
?>
