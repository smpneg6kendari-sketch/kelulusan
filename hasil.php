<?php
/**
 * Halaman Hasil Kelulusan
 */
session_start();
require_once 'config/koneksi.php';

$pengaturan = getPengaturan($conn);
$dibuka = isPengumumanDibuka($pengaturan);
if (!$dibuka) { header('Location: index.php'); exit; }

$nisn = trim($_GET['nisn'] ?? '');
if (empty($nisn)) { header('Location: cek.php'); exit; }

$murid = cariMurid($conn, $nisn);
if (!$murid) { header('Location: cek.php'); exit; }

$lulus = ($murid['status_kelulusan'] === 'LULUS');
$tahunAjaran = $pengaturan['tahun_ajaran'] ?? '2024/2025';
$namaSekolah = $pengaturan['nama_sekolah'] ?? 'SMP Negeri 6 Kendari';
$alamatSekolah = $pengaturan['alamat_sekolah'] ?? '';
$namaKepsek = $pengaturan['nama_kepsek'] ?? '';
$nipKepsek = $pengaturan['nip_kepsek'] ?? '';
$logoSekolah = !empty($pengaturan['logo']) ? $pengaturan['logo'] : 'assets/images/logo_sekolah.png';
$pageTitle = $lulus ? 'Selamat Lulus!' : 'Hasil Kelulusan';
$pesanKelulusan = $lulus ? getRandomPesanKelulusan($conn) : '';

include 'includes/header.php';
?>

<div id="loadingOverlay" class="loading-overlay">
    <div class="text-center">
        <div class="spinner mx-auto mb-4"></div>
        <p class="text-primary font-semibold text-sm">Memuat hasil...</p>
    </div>
</div>

<?php if ($lulus): ?>
<div id="confettiArea"></div>
<?php endif; ?>

<div class="page-wrapper">
    <!-- HEADER -->
    <header class="hero-gradient app-topbar relative px-5 pt-5 pb-10 overflow-hidden">
        <div class="flex items-center justify-between relative z-10 mb-6 fade-in-up">
            <button onclick="navigateTo('cek.php')" class="flex items-center gap-2 text-white font-semibold text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Kembali
            </button>
            <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center overflow-hidden">
                <img src="<?= htmlspecialchars($logoSekolah) ?>" alt="Logo" class="w-8 h-8 object-cover">
            </div>
        </div>
    </header>

        <!-- Badge -->
    <!-- MAIN -->
    <main class="content-panel result-panel px-5 pt-12 pb-8 relative z-10 space-y-5" id="hasilContent">
        <span id="namaSekolahSkl" class="hidden"><?= htmlspecialchars($namaSekolah) ?></span>
        <span id="alamatSekolahSkl" class="hidden"><?= htmlspecialchars($alamatSekolah) ?></span>
        <span id="namaKepsekSkl" class="hidden"><?= htmlspecialchars($namaKepsek) ?></span>
        <span id="nipKepsekSkl" class="hidden"><?= htmlspecialchars($nipKepsek) ?></span>
        <div class="text-center relative z-10">
            <div class="badge-bounce result-icon-badge">
                <div class="result-medal <?= $lulus ? 'result-medal-success' : 'result-medal-danger' ?>">
                    <?php if ($lulus): ?>
                    <lottie-player
                        class="result-lottie"
                        src="assets/images/medal.json"
                        background="transparent"
                        speed="1"
                        autoplay
                        loop
                        aria-label="Animasi medali kelulusan">
                    </lottie-player>
                    <?php else: ?>
                    <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($lulus): ?>
            <div class="ribbon ribbon-3d mb-3">SELAMAT</div>
            <h1 class="text-success text-[2rem] leading-tight font-black fade-in-up delay-200">ANDA LULUS!</h1>
            <?php else: ?>
            <h1 class="text-danger text-2xl font-extrabold fade-in-up delay-200 mb-1">Mohon Maaf</h1>
            <p class="text-text-dark text-xl font-extrabold fade-in-up delay-300">Anda Belum Lulus</p>
            <?php endif; ?>
            <p class="text-text-dark text-sm mt-2 fade-in-up delay-300">Tahun Ajaran <span id="tahunAjaran"><?= htmlspecialchars($tahunAjaran) ?></span></p>
        </div>

        <!-- Data Card -->
        <div class="data-card rounded-2xl p-5 fade-in-up delay-400">
            <div class="space-y-4">
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-text-muted">Nama Murid</span>
                    <span class="text-sm font-bold text-text-dark" id="namaMurid"><?= htmlspecialchars($murid['nama']) ?></span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-text-muted">NISN</span>
                    <span class="text-sm font-bold text-text-dark" id="nisnMurid"><?= htmlspecialchars($murid['nisn']) ?></span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-text-muted">Kelas</span>
                    <span class="text-sm font-bold text-text-dark" id="kelasMurid"><?= htmlspecialchars($murid['kelas']) ?></span>
                </div>
                <div class="flex justify-between items-center py-2">
                    <span class="text-sm text-text-muted">Status</span>
                    <?php if ($lulus): ?>
                    <span class="status-pill status-success px-4 py-1 rounded-lg text-xs font-bold">LULUS</span>
                    <?php else: ?>
                    <span class="status-pill status-danger px-4 py-1 rounded-lg text-xs font-bold">TIDAK LULUS</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Motivasi Card -->
        <?php if ($lulus): ?>
        <div class="notice-card notice-success rounded-2xl p-5 fade-in-up delay-500">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-full bg-success/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-5 h-5 text-success" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                </div>
                <p class="text-sm text-success font-medium leading-relaxed whitespace-pre-line"><?= htmlspecialchars($pesanKelulusan) ?></p>
            </div>
        </div>
        <?php else: ?>
        <div class="notice-card notice-danger rounded-2xl p-5 fade-in-up delay-500">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-full bg-danger/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-5 h-5 text-danger" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/></svg>
                </div>
                <p class="text-sm text-danger font-medium leading-relaxed">Jangan menyerah. Teruslah belajar dan berusaha. Kesuksesan menanti Anda!</p>
            </div>
        </div>

        <div class="notice-card rounded-2xl p-5 fade-in-up delay-600">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-sm text-text-muted leading-relaxed">Informasi lebih lanjut silakan hubungi pihak sekolah.</p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="fade-in-up delay-600 no-print space-y-3">
            <?php if ($lulus): ?>
            <div class="grid grid-cols-2 gap-3">
                <button onclick="downloadSKL()" class="btn-3d py-3.5 rounded-2xl text-white font-bold text-sm flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Unduh SKL
                </button>
                <button onclick="printResult()" class="btn-outline-3d py-3.5 rounded-2xl bg-white border-2 border-primary text-primary font-bold text-sm flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak Hasil
                </button>
            </div>
            <button onclick="navigateTo('cek.php')" class="btn-soft-3d w-full py-3.5 rounded-2xl text-primary font-semibold text-sm flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Cek Hasil Lainnya
            </button>
            <?php else: ?>
            <a href="tel:+6281234567890" class="btn-3d w-full py-4 rounded-2xl text-white font-bold text-base flex items-center justify-center gap-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                Hubungi Sekolah
            </a>
            <button onclick="navigateTo('cek.php')" class="btn-outline-3d w-full py-3.5 rounded-2xl bg-white border-2 border-gray-200 text-text-dark font-semibold text-sm flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Cek Hasil Lainnya
            </button>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php if ($lulus): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(launchConfetti, 800);
    setTimeout(launchConfetti, 2500);
});
</script>
<?php endif; ?>

<?php
if ($lulus) {
    $extraJS = ($extraJS ?? '') . '<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>';
}
?>
<?php include 'includes/footer.php'; ?>
