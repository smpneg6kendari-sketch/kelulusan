<?php
/**
 * Landing Page - Pengumuman Kelulusan
 * SMP Negeri 6 Kendari
 */
session_start();
require_once 'config/koneksi.php';
setlocale(LC_TIME, 'id_ID.UTF-8', 'id_ID', 'Indonesian');

$pengaturan = getPengaturan($conn);
$dibuka = isPengumumanDibuka($pengaturan);

$namaSekolah = $pengaturan['nama_sekolah'] ?? 'SMP Negeri 6 Kendari';
$namaSekolahUpper = strtoupper($namaSekolah);
$namaKepsek = $pengaturan['nama_kepsek'] ?? 'Drs. Andi Prasetyo';
$tahunAjaran = $pengaturan['tahun_ajaran'] ?? '2024/2025';
$sambutan = $pengaturan['sambutan'] ?? '';
$sambutanPlain = trim($sambutan);
$sambutanLength = function_exists('mb_strlen') ? mb_strlen($sambutanPlain) : strlen($sambutanPlain);
$showSambutanMore = $sambutanLength > 420 || substr_count($sambutanPlain, "\n") > 7;
$tglPengumuman = $pengaturan['tanggal_pengumuman'] ?? '2025-05-27 10:00:00';
$logoSekolah = !empty($pengaturan['logo']) ? $pengaturan['logo'] : 'assets/images/logo_sekolah.png';
$fotoKepsek = !empty($pengaturan['foto_kepsek']) ? $pengaturan['foto_kepsek'] : 'assets/images/kepala_sekolah.png';

// Helper format tanggal Indonesia
function formatTanggalIndo($dateStr) {
    $hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    $bulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $ts = strtotime($dateStr);
    $h = $hari[date('w', $ts)];
    $d = date('j', $ts);
    $m = $bulan[(int)date('n', $ts)];
    $y = date('Y', $ts);
    return "$h, $d $m $y";
}

$pageTitle = 'Beranda';
include 'includes/header.php';
?>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="loading-overlay">
    <div class="text-center">
        <div class="spinner mx-auto mb-4"></div>
        <p class="text-primary font-semibold text-sm">Memuat halaman...</p>
    </div>
</div>

<!-- Mobile Nav Overlay -->
<div id="navOverlay" class="nav-overlay fixed inset-0 bg-black/40 z-40" onclick="toggleNav()"></div>

<!-- Mobile Nav -->
<nav id="mobileNav" class="mobile-nav fixed top-0 right-0 h-full w-72 bg-white z-50 shadow-2xl">
    <div class="p-6">
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-3">
                <img src="<?= htmlspecialchars($logoSekolah) ?>" alt="Logo" class="w-10 h-10 rounded-xl object-cover">
                <div>
                    <p class="font-bold text-sm text-text-dark"><?= htmlspecialchars($namaSekolahUpper) ?></p>
                </div>
            </div>
            <button onclick="toggleNav()" class="w-9 h-9 rounded-full bg-bg-main flex items-center justify-center">
                <svg class="w-5 h-5 text-text-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="space-y-2">
            <a href="index.php" class="flex items-center gap-3 p-3 rounded-2xl bg-primary/5 text-primary font-semibold text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Beranda
            </a>
            <a href="cek.php" class="flex items-center gap-3 p-3 rounded-2xl hover:bg-bg-main text-text-dark font-medium text-sm transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Cek Kelulusan
            </a>
        </div>
    </div>
</nav>

<div class="page-wrapper">
    <!-- ===== HEADER ===== -->
    <header class="hero-gradient app-topbar relative px-5 pt-5 pb-8 overflow-hidden">
        <!-- Clouds -->
        <div class="cloud cloud-1"></div>
        <div class="cloud cloud-2"></div>
        <div class="cloud cloud-3"></div>

        <!-- Top Bar -->
        <div class="flex items-center justify-between relative z-10 mb-8 fade-in-up">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center overflow-hidden">
                    <img src="<?= htmlspecialchars($logoSekolah) ?>" alt="Logo" class="w-8 h-8 object-cover">
                </div>
                <div>
                    <p class="text-white font-bold text-sm tracking-wide"><?= htmlspecialchars($namaSekolahUpper) ?></p>
                </div>
            </div>
            <button onclick="toggleNav()" class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center" id="btnMenu">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
    </header>

    <!-- ===== MAIN CONTENT ===== -->
    <main class="content-panel px-5 pt-8 pb-7 relative z-10 space-y-5">

        <!-- Hero Content -->
        <div class="text-center relative z-10">
            <h1 class="text-text-dark text-[1.7rem] leading-tight font-extrabold mb-2 fade-in-up delay-100">Pengumuman Kelulusan</h1>
            <p class="text-primary text-[1.55rem] leading-tight font-black tracking-wide fade-in-up delay-200"><?= htmlspecialchars($namaSekolahUpper) ?></p>
            <p class="text-primary-dark text-base font-bold mt-2 fade-in-up delay-300">Tahun Ajaran <?= htmlspecialchars($tahunAjaran) ?></p>
            
            <!-- Principal Photo -->
            <div class="flex justify-center mt-5 fade-in-up delay-400">
                <div class="avatar-ring principal-avatar">
                    <div class="w-32 h-32 rounded-full overflow-hidden bg-white">
                        <img src="<?= htmlspecialchars($fotoKepsek) ?>" alt="Kepala Sekolah" class="w-full h-full object-cover">
                    </div>
                </div>
            </div>
        </div>

        <!-- Sambutan Card -->
        <div class="soft-card rounded-3xl p-6 fade-in-up delay-400">
            <div class="text-center mb-4">
                <h2 class="font-bold text-base text-text-dark">Sambutan Kepala Sekolah</h2>
                <p class="text-primary text-sm font-semibold mt-1"><?= htmlspecialchars($namaKepsek) ?></p>
            </div>
            <div class="sambutan-preview <?= $showSambutanMore ? 'is-collapsed' : '' ?>">"<?= htmlspecialchars($sambutanPlain) ?>"</div>
            <?php if ($showSambutanMore): ?>
            <button type="button" onclick="openSambutanModal()" class="btn-soft-3d mt-4 mx-auto px-4 py-2 rounded-2xl text-primary text-xs font-bold flex items-center justify-center">
                Lihat selengkapnya
            </button>
            <?php endif; ?>
        </div>

        <!-- Jadwal Card -->
        <div class="soft-card rounded-3xl p-6 fade-in-up delay-500">
            <div class="flex items-start gap-4">
                <div class="icon-tile w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-text-muted text-xs mb-1">Pengumuman akan dibuka pada:</p>
                    <p class="font-bold text-text-dark text-base">
                        <?= formatTanggalIndo($tglPengumuman) ?>
                    </p>
                    <p class="text-primary font-bold text-lg"><?= date('H:i', strtotime($tglPengumuman)) ?> WITA</p>
                </div>
            </div>
        </div>

        <?php if (!$dibuka): ?>
        <!-- Countdown Section -->
        <div id="countdownSection" class="soft-card rounded-3xl p-6 fade-in-up delay-500">
            <p class="text-center text-sm font-semibold text-text-dark mb-4">Waktu Menuju Pengumuman</p>
            <div class="flex justify-center gap-3">
                <div class="countdown-digit">
                    <p id="countDays" class="text-2xl font-extrabold">00</p>
                    <p class="text-[10px] font-medium opacity-80 mt-1">Hari</p>
                </div>
                <div class="countdown-digit">
                    <p id="countHours" class="text-2xl font-extrabold">00</p>
                    <p class="text-[10px] font-medium opacity-80 mt-1">Jam</p>
                </div>
                <div class="countdown-digit">
                    <p id="countMins" class="text-2xl font-extrabold">00</p>
                    <p class="text-[10px] font-medium opacity-80 mt-1">Menit</p>
                </div>
                <div class="countdown-digit">
                    <p id="countSecs" class="text-2xl font-extrabold">00</p>
                    <p class="text-[10px] font-medium opacity-80 mt-1">Detik</p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- CTA Button -->
        <div class="fade-in-up delay-600" id="mainContent">
            <?php if ($dibuka): ?>
                <button onclick="navigateTo('cek.php')" class="btn-3d w-full py-4 rounded-2xl text-white font-bold text-base flex items-center justify-center gap-3" id="btnLihatPengumuman">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Lihat Pengumuman
                </button>
            <?php else: ?>
                <div class="blur-lock">
                    <button disabled class="btn-3d w-full py-4 rounded-2xl text-white font-bold text-base flex items-center justify-center gap-3 opacity-50 cursor-not-allowed">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        Pengumuman Belum Dibuka
                    </button>
                </div>
                <p class="text-center text-xs text-text-muted mt-3">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Halaman akan terbuka otomatis saat waktu pengumuman tiba
                </p>
            <?php endif; ?>
        </div>

        <!-- Mini Info Cards -->
        <div class="feature-strip grid grid-cols-3 gap-0 fade-in-up delay-700">
            <div class="feature-item p-4 text-center">
                <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center mx-auto mb-2">
                    <svg class="w-5 h-5 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <p class="font-bold text-xs text-text-dark">Aman</p>
                <p class="text-[10px] text-text-muted mt-0.5">Data terjamin kerahasiaannya</p>
            </div>
            <div class="feature-item p-4 text-center">
                <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center mx-auto mb-2">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="font-bold text-xs text-text-dark">Tepat Waktu</p>
                <p class="text-[10px] text-text-muted mt-0.5">Informasi sesuai jadwal resmi</p>
            </div>
            <div class="feature-item p-4 text-center">
                <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center mx-auto mb-2">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="font-bold text-xs text-text-dark">Mudah</p>
                <p class="text-[10px] text-text-muted mt-0.5">Cek kelulusan kapan saja</p>
            </div>
        </div>

    </main>
</div>

<?php if ($showSambutanMore): ?>
<div id="sambutanModal" class="modal-backdrop" aria-hidden="true">
    <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="sambutanModalTitle">
        <div class="flex items-start justify-between gap-4 mb-4">
            <div>
                <h2 id="sambutanModalTitle" class="font-extrabold text-text-dark text-lg">Sambutan Kepala Sekolah</h2>
                <p class="text-primary text-sm font-semibold mt-1"><?= htmlspecialchars($namaKepsek) ?></p>
            </div>
            <button type="button" onclick="closeSambutanModal()" class="modal-close" aria-label="Tutup">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="sambutan-full">"<?= htmlspecialchars($sambutanPlain) ?>"</div>
    </div>
</div>
<?php endif; ?>

<?php if (!$dibuka): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // +08:00 = WITA, agar countdown benar di semua timezone browser
        initCountdown('<?= date('Y-m-d\TH:i:s', strtotime($tglPengumuman)) ?>+08:00');
    });
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
