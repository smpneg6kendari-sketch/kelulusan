<?php
/**
 * Halaman Cek Kelulusan - Input NISN
 */
session_start();
require_once 'config/koneksi.php';

$pengaturan = getPengaturan($conn);
$dibuka = isPengumumanDibuka($pengaturan);
$logoSekolah = !empty($pengaturan['logo']) ? $pengaturan['logo'] : 'assets/images/logo_sekolah.png';

if (!$dibuka) { header('Location: index.php'); exit; }

$pageTitle = 'Cek Kelulusan';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nisn = trim($_POST['nisn'] ?? '');
    if (empty($nisn)) {
        $error = 'Silakan masukkan NISN Anda.';
    } elseif (!preg_match('/^\d{10}$/', $nisn)) {
        $error = 'Format NISN tidak valid. NISN terdiri dari 10 digit angka.';
    } else {
        $murid = cariMurid($conn, $nisn);
        if ($murid) {
            header('Location: hasil.php?nisn=' . urlencode($nisn));
            exit;
        } else {
            $error = 'Data tidak ditemukan. Pastikan NISN yang Anda masukkan benar.';
        }
    }
}

include 'includes/header.php';
?>

<div id="loadingOverlay" class="loading-overlay">
    <div class="text-center">
        <div class="spinner mx-auto mb-4"></div>
        <p class="text-primary font-semibold text-sm">Memuat...</p>
    </div>
</div>

<div class="page-wrapper">
    <header class="hero-gradient app-topbar relative px-5 pt-5 pb-10 overflow-hidden">
        <div class="cloud cloud-1"></div>
        <div class="cloud cloud-2"></div>
        <div class="flex items-center justify-between relative z-10 mb-6 fade-in-up">
            <button onclick="navigateTo('index.php')" class="flex items-center gap-2 text-white font-semibold text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Kembali
            </button>
            <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center overflow-hidden">
                <img src="<?= htmlspecialchars($logoSekolah) ?>" alt="Logo" class="w-8 h-8 object-cover">
            </div>
        </div>
    </header>

    <main class="content-panel check-panel px-5 pt-24 pb-8 relative z-10 space-y-5">
        <div class="floating-hero-icon">
            <svg class="w-16 h-16 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M10 21h7a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v11m0 5l4.879-4.879m0 0a3 3 0 104.243-4.242 3 3 0 00-4.243 4.242z"/>
            </svg>
        </div>

        <div class="form-card p-2 fade-in-up delay-200">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-extrabold text-text-dark mb-3">Cek Kelulusan</h1>
                <p class="text-base leading-relaxed text-text-muted">Masukkan NISN Anda<br>untuk melihat hasil kelulusan</p>
            </div>

            <?php if ($error): ?>
            <div class="notice-card notice-danger rounded-2xl p-4 mb-5 flex items-start gap-3 fade-in-up">
                <div class="w-8 h-8 rounded-full bg-danger/10 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-4 h-4 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-sm text-danger font-medium"><?= htmlspecialchars($error) ?></p>
            </div>
            <?php endif; ?>

            <form method="POST" action="cek.php" id="formCek">
                <div class="mb-5">
                    <label class="block text-xs font-semibold text-text-muted mb-2 uppercase tracking-wider">NISN</label>
                    <div class="relative">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2">
                            <svg class="w-5 h-5 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <input type="text" name="nisn" id="inputNISN" class="input-modern field-3d w-full pl-12 pr-4 py-4 rounded-2xl border border-gray-200 bg-white text-base font-medium text-text-dark placeholder-text-muted/50 focus:outline-none" placeholder="Masukkan NISN Anda" maxlength="10" inputmode="numeric" pattern="[0-9]*" oninput="validateNISN(this)" value="<?= htmlspecialchars($_POST['nisn'] ?? '') ?>" required autocomplete="off">
                    </div>
                    <p class="text-xs text-text-muted mt-2 ml-1">NISN terdiri dari 10 digit angka</p>
                </div>
                <button type="submit" class="btn-3d w-full py-4 rounded-2xl text-white font-bold text-base flex items-center justify-center gap-3" id="btnCek">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Cek Hasil Kelulusan
                </button>
            </form>
        </div>

        <div class="notice-card rounded-3xl p-5 fade-in-up delay-300">
            <div class="flex items-start gap-4">
                <div class="icon-tile w-11 h-11 rounded-2xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <div>
                    <p class="font-bold text-sm text-text-dark mb-1">Perhatian</p>
                    <p class="text-xs text-text-muted leading-relaxed">Data bersifat rahasia dan hanya dapat diakses oleh yang bersangkutan.</p>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
document.getElementById('formCek')?.addEventListener('submit', function() {
    const btn = document.getElementById('btnCek');
    btn.innerHTML = '<div class="spinner w-5 h-5 border-2 border-white/30 border-t-white"></div> Mencari data...';
    btn.disabled = true;
});
</script>

<?php include 'includes/footer.php'; ?>
