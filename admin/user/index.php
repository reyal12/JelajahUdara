<?php
$page_title = "Kelola User";
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

$error_msg = null;
$success_msg = null;

// Handle CRUD Operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add User
    if (isset($_POST['add_user'])) {
        $nama = filter_input(INPUT_POST, 'nama_lengkap', FILTER_SANITIZE_SPECIAL_CHARS);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_SPECIAL_CHARS);

        if (empty($nama) || empty($email) || empty($password) || empty($role)) {
            $error_msg = "Semua kolom harus diisi!";
        } elseif (strlen($password) < 6) {
            $error_msg = "Kata sandi minimal 6 karakter!";
        } else {
            try {
                // Check duplicate email
                $stmt_chk = $db->prepare("SELECT id_user FROM users WHERE email = :email LIMIT 1");
                $stmt_chk->execute([':email' => $email]);
                
                if ($stmt_chk->rowCount() > 0) {
                    $error_msg = "Email sudah digunakan oleh pengguna lain!";
                } else {
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                    $query = "INSERT INTO users (nama, nama_lengkap, email, password, role) VALUES (:nama, :nama, :email, :password, :role)";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':nama', $nama);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':password', $hashed_password);
                    $stmt->bindParam(':role', $role);

                    if ($stmt->execute()) {
                        $success_msg = "User baru berhasil ditambahkan!";
                    } else {
                        $error_msg = "Gagal menambahkan user.";
                    }
                }
            } catch (PDOException $e) {
                $error_msg = "Kesalahan database: " . $e->getMessage();
            }
        }
    }

    // Edit User
    if (isset($_POST['edit_user'])) {
        $id = intval($_POST['id_user']);
        $nama = filter_input(INPUT_POST, 'nama_lengkap', FILTER_SANITIZE_SPECIAL_CHARS);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_SPECIAL_CHARS);

        if (empty($nama) || empty($email) || empty($role)) {
            $error_msg = "Nama lengkap, email, dan role tidak boleh kosong!";
        } else {
            try {
                // Check duplicate email
                $stmt_chk = $db->prepare("SELECT id_user FROM users WHERE email = :email AND id_user != :id LIMIT 1");
                $stmt_chk->execute([':email' => $email, ':id' => $id]);

                if ($stmt_chk->rowCount() > 0) {
                    $error_msg = "Email sudah digunakan oleh pengguna lain!";
                } else {
                    if (!empty($password)) {
                        if (strlen($password) < 6) {
                            $error_msg = "Kata sandi baru minimal 6 karakter!";
                        } else {
                            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                            $query = "UPDATE users SET nama = :nama, nama_lengkap = :nama, email = :email, password = :password, role = :role WHERE id_user = :id";
                            $stmt = $db->prepare($query);
                            $stmt->bindParam(':password', $hashed_password);
                        }
                    } else {
                        $query = "UPDATE users SET nama = :nama, nama_lengkap = :nama, email = :email, role = :role WHERE id_user = :id";
                        $stmt = $db->prepare($query);
                    }

                    if (!isset($error_msg)) {
                        $stmt->bindParam(':nama', $nama);
                        $stmt->bindParam(':email', $email);
                        $stmt->bindParam(':role', $role);
                        $stmt->bindParam(':id', $id);

                        if ($stmt->execute()) {
                            $success_msg = "Data user berhasil diperbarui!";
                        } else {
                            $error_msg = "Gagal memperbarui data user.";
                        }
                    }
                }
            } catch (PDOException $e) {
                $error_msg = "Kesalahan database: " . $e->getMessage();
            }
        }
    }
}

// Delete User
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Prevent admin deleting themselves
    if ($id === intval($_SESSION['user_id'])) {
        $error_msg = "Anda tidak bisa menghapus akun Anda sendiri yang sedang aktif!";
    } else {
        try {
            $query = "DELETE FROM users WHERE id_user = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                $success_msg = "User berhasil dihapus!";
            } else {
                $error_msg = "Gagal menghapus user.";
            }
        } catch (PDOException $e) {
            $error_msg = "Kesalahan database atau dependensi data: " . $e->getMessage();
        }
    }
}

// Fetch single User for Edit mode
$edit_data = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    try {
        $query = "SELECT * FROM users WHERE id_user = :id LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {}
}

// Search & List Users
$search = isset($_GET['search']) ? filter_input(INPUT_GET, 'search', FILTER_SANITIZE_SPECIAL_CHARS) : '';
$users_list = [];
try {
    if (!empty($search)) {
        $query = "SELECT * FROM users 
                  WHERE nama_lengkap LIKE :search 
                     OR email LIKE :search 
                     OR role LIKE :search 
                  ORDER BY nama_lengkap ASC";
        $stmt = $db->prepare($query);
        $search_param = "%" . $search . "%";
        $stmt->bindParam(':search', $search_param);
    } else {
        $query = "SELECT * FROM users ORDER BY nama_lengkap ASC";
        $stmt = $db->prepare($query);
    }
    $stmt->execute();
    $users_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
?>

<div class="admin-wrapper">
    <!-- Include Sidebar -->
    <?php require_once '../../includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <main class="admin-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Kelola User</h3>
                <p class="text-muted mb-0">Manajemen akun Administrator dan Customer JelajahUdara.</p>
            </div>
        </div>

        <?php if ($error_msg): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i> <?= $error_msg ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($success_msg): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i> <?= $success_msg ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Form Card (Add/Edit) -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <h5 class="fw-bold text-secondary mb-3 pb-2 border-bottom">
                        <?= $edit_data ? '<i class="fa-solid fa-user-pen text-warning me-2"></i> Edit User' : '<i class="fa-solid fa-user-plus text-primary me-2"></i> Tambah User' ?>
                    </h5>
                    
                    <form action="index.php" method="POST">
                        <?php if ($edit_data): ?>
                            <input type="hidden" name="id_user" value="<?= $edit_data['id_user'] ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="nama_lengkap" class="form-label fw-semibold">Nama Lengkap</label>
                            <input type="text" class="form-control bg-light" id="nama_lengkap" name="nama_lengkap" value="<?= htmlspecialchars($edit_data['nama_lengkap'] ?? '') ?>" placeholder="Masukkan nama lengkap" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email</label>
                            <input type="email" class="form-control bg-light" id="email" name="email" value="<?= htmlspecialchars($edit_data['email'] ?? '') ?>" placeholder="nama@email.com" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">Kata Sandi</label>
                            <input type="password" class="form-control bg-light" id="password" name="password" placeholder="<?= $edit_data ? 'Kosongkan jika tidak diubah' : 'Minimal 6 karakter' ?>" <?= $edit_data ? '' : 'required' ?> minlength="6">
                        </div>

                        <div class="mb-4">
                            <label for="role" class="form-label fw-semibold">Role / Hak Akses</label>
                            <select class="form-select bg-light" id="role" name="role" required>
                                <option value="customer" <?= (isset($edit_data['role']) && $edit_data['role'] === 'customer') ? 'selected' : '' ?>>Customer</option>
                                <option value="admin" <?= (isset($edit_data['role']) && $edit_data['role'] === 'admin') ? 'selected' : '' ?>>Admin</option>
                            </select>
                        </div>

                        <button type="submit" name="<?= $edit_data ? 'edit_user' : 'add_user' ?>" class="btn <?= $edit_data ? 'btn-warning' : 'btn-primary' ?> w-100 fw-bold">
                            <?= $edit_data ? '<i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan' : '<i class="fa-solid fa-plus me-1"></i> Tambah User' ?>
                        </button>
                        
                        <?php if ($edit_data): ?>
                            <a href="index.php" class="btn btn-outline-secondary w-100 fw-bold mt-2">Batal</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- List Card -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3 border-bottom pb-2">
                        <h5 class="fw-bold text-secondary mb-0">Daftar Pengguna</h5>
                        <form action="" method="GET" class="d-flex" style="max-width: 300px;">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control bg-light" placeholder="Cari nama, email, role..." value="<?= htmlspecialchars($search) ?>">
                                <button class="btn btn-primary" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Nama Lengkap</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Tanggal Daftar</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($users_list)): ?>
                                    <?php foreach ($users_list as $u): ?>
                                        <tr>
                                            <td><?= $u['id_user'] ?></td>
                                            <td><strong><?= htmlspecialchars($u['nama_lengkap']) ?></strong></td>
                                            <td><?= htmlspecialchars($u['email']) ?></td>
                                            <td>
                                                <span class="badge <?= $u['role'] === 'admin' ? 'bg-danger-subtle text-danger' : 'bg-primary-subtle text-primary' ?> px-2.5 py-1.5 rounded-pill text-capitalize">
                                                    <?= htmlspecialchars($u['role']) ?>
                                                </span>
                                            </td>
                                            <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                                            <td class="text-center">
                                                <a href="index.php?action=edit&id=<?= $u['id_user'] ?>" class="btn btn-outline-warning btn-sm me-1" title="Edit">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                                <?php if ($u['id_user'] !== intval($_SESSION['user_id'])): ?>
                                                    <a href="index.php?action=delete&id=<?= $u['id_user'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus user ini?');" title="Hapus">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <button class="btn btn-light btn-sm text-muted" disabled><i class="fa-solid fa-trash"></i></button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">User tidak ditemukan.</td>
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
