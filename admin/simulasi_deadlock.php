<?php
$page_title = "Simulasi Deadlock";
require_once '../includes/header.php';

// Authentication & Role Check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error'] = "Akses ditolak! Anda harus masuk sebagai Admin.";
    header("Location: ../auth/login.php");
    exit();
}

$is_admin_layout = true;
?>

<div class="admin-wrapper">
    <!-- Include Sidebar -->
    <?php require_once '../includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <main class="admin-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Simulasi Database Deadlock</h3>
                <p class="text-muted mb-0">Demonstrasi terjadinya perebutan resource (race condition) pada transaksi database.</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <h5 class="fw-bold text-secondary mb-3"><i class="fa-solid fa-circle-info text-primary me-2"></i> Apa itu Deadlock?</h5>
                    <p class="text-muted">
                        <strong>Deadlock</strong> terjadi ketika dua atau lebih transaksi saling mengunci data yang dibutuhkan satu sama lain untuk bisa selesai.
                    </p>
                    <ul class="text-muted small mb-4 text-start">
                        <li class="mb-2"><strong>Transaksi 1</strong> mengunci Penerbangan A, lalu menunggu Penerbangan B terbuka.</li>
                        <li class="mb-2"><strong>Transaksi 2</strong> mengunci Penerbangan B, lalu menunggu Penerbangan A terbuka.</li>
                    </ul>
                    <p class="text-muted small">
                        MySQL memiliki mekanisme otomatis untuk mendeteksi jalan buntu ini. Ia akan membatalkan (<strong>Rollback</strong>) salah satu transaksi agar transaksi yang lain bisa dilanjutkan. 
                        Hal ini aman karena tidak merusak konsistensi data.
                    </p>

                    <hr>

                    <button id="btnRunDeadlock" class="btn btn-danger btn-lg w-100 py-3 fw-bold mt-2" onclick="runSimulation()">
                        <i class="fa-solid fa-play me-2"></i> Jalankan Simulasi Sekarang
                    </button>
                    
                    <button id="btnReset" class="btn btn-outline-secondary w-100 py-2 mt-2 d-none" onclick="resetUI()">
                        <i class="fa-solid fa-rotate-right me-2"></i> Bersihkan Log & Ulangi
                    </button>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 bg-dark h-100 overflow-hidden">
                    <div class="card-header bg-dark border-bottom border-secondary p-3 d-flex justify-content-between align-items-center">
                        <span class="text-white fw-bold"><i class="fa-solid fa-terminal text-success me-2"></i> Terminal Log</span>
                        <div class="d-flex gap-2">
                            <span class="badge bg-secondary" id="statusBadge">Siap</span>
                        </div>
                    </div>
                    <div class="card-body p-0 position-relative">
                        <div id="terminal" class="p-4" style="height: 350px; overflow-y: auto; background-color: #1e1e1e; font-family: monospace; font-size: 0.9rem;">
                            <div class="text-secondary mb-2">&gt; Sistem siap. Tekan tombol untuk memulai simulasi...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
function appendLog(message, type = 'info') {
    const terminal = document.getElementById('terminal');
    const time = new Date().toLocaleTimeString('id-ID');
    
    let colorClass = 'text-white';
    if (type === 'success') colorClass = 'text-success';
    if (type === 'error' || type === 'deadlock') colorClass = 'text-danger';
    if (type === 'warning') colorClass = 'text-warning';
    if (type === 'system') colorClass = 'text-secondary';
    
    const logEntry = document.createElement('div');
    logEntry.className = `mb-1 ${colorClass}`;
    logEntry.innerHTML = `<span class="text-secondary">[${time}]</span> &gt; ${message}`;
    
    terminal.appendChild(logEntry);
    terminal.scrollTop = terminal.scrollHeight;
}

async function runSimulation() {
    const btnRun = document.getElementById('btnRunDeadlock');
    const btnReset = document.getElementById('btnReset');
    const badge = document.getElementById('statusBadge');
    
    btnRun.disabled = true;
    btnRun.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Memproses...';
    
    badge.className = 'badge bg-warning text-dark';
    badge.innerText = 'Berjalan';
    
    appendLog('Memulai simulasi deadlock...', 'system');
    appendLog('Menjalankan Transaksi 1 dan Transaksi 2 secara bersamaan...', 'info');

    try {
        // Run both requests concurrently using Promise.all
        const [res1, res2] = await Promise.allSettled([
            fetch('process_deadlock.php?tx=1').then(r => r.json()),
            fetch('process_deadlock.php?tx=2').then(r => r.json())
        ]);

        // Process response for Tx 1
        if (res1.status === 'fulfilled') {
            const data1 = res1.value;
            if (data1.status === 'deadlock') appendLog(data1.message, 'deadlock');
            else if (data1.status === 'success') appendLog(data1.message, 'success');
            else appendLog(data1.message, 'error');
        } else {
            appendLog('Koneksi Transaksi 1 gagal.', 'error');
        }

        // Process response for Tx 2
        if (res2.status === 'fulfilled') {
            const data2 = res2.value;
            if (data2.status === 'deadlock') appendLog(data2.message, 'deadlock');
            else if (data2.status === 'success') appendLog(data2.message, 'success');
            else appendLog(data2.message, 'error');
        } else {
            appendLog('Koneksi Transaksi 2 gagal.', 'error');
        }
        
        appendLog('Simulasi selesai.', 'system');
        badge.className = 'badge bg-success';
        badge.innerText = 'Selesai';
        
    } catch (error) {
        appendLog('Terjadi kesalahan koneksi sistem.', 'error');
        badge.className = 'badge bg-danger';
        badge.innerText = 'Error';
    } finally {
        btnRun.classList.add('d-none');
        btnReset.classList.remove('d-none');
    }
}

function resetUI() {
    const terminal = document.getElementById('terminal');
    const btnRun = document.getElementById('btnRunDeadlock');
    const btnReset = document.getElementById('btnReset');
    const badge = document.getElementById('statusBadge');
    
    terminal.innerHTML = '<div class="text-secondary mb-2">&gt; Sistem siap. Tekan tombol untuk memulai simulasi...</div>';
    
    badge.className = 'badge bg-secondary';
    badge.innerText = 'Siap';
    
    btnRun.disabled = false;
    btnRun.innerHTML = '<i class="fa-solid fa-play me-2"></i> Jalankan Simulasi Sekarang';
    
    btnRun.classList.remove('d-none');
    btnReset.classList.add('d-none');
}
</script>

<?php require_once '../includes/footer.php'; ?>
