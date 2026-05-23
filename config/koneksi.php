<?php
/**
 * Konfigurasi Koneksi Database
 * Website Pengumuman Kelulusan
 */

// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'afifeduc_kelulusan');
define('DB_PASS', '30041982aA@');
define('DB_NAME', 'afifeduc_kelulusan');

// Konfigurasi Website
define('SITE_NAME', 'Pengumuman Kelulusan');
define('BASE_URL', '/');

// Set Timezone WITA (Waktu Indonesia Tengah = UTC+8)
date_default_timezone_set('Asia/Makassar');

// Matikan exception otomatis MySQLi (penting untuk PHP 8+)
mysqli_report(MYSQLI_REPORT_OFF);

// Koneksi menggunakan MySQLi
$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if ($conn->connect_errno) {
    // Tampilkan halaman error yang ramah, bukan crash
    http_response_code(503);
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Gangguan Layanan</title>
    <script src="https://cdn.tailwindcss.com"></script></head>
    <body class="bg-blue-50 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white rounded-3xl p-8 max-w-sm w-full text-center shadow-xl">
        <div class="text-5xl mb-4">🔧</div>
        <h1 class="text-xl font-bold text-gray-800 mb-2">Sedang Dalam Perbaikan</h1>
        <p class="text-gray-500 text-sm">Sistem sedang mengalami gangguan teknis. Silakan coba beberapa saat lagi.</p>
    </div></body></html>';
    exit;
}

// Set charset
$conn->set_charset("utf8mb4");

/**
 * Ambil pengaturan website
 */
function getPengaturan($conn) {
    $stmt = $conn->prepare("SELECT * FROM pengaturan WHERE is_active = 1 LIMIT 1");
    if (!$stmt) return null;
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    return $data;
}

/**
 * Cari murid berdasarkan NISN
 */
function cariMurid($conn, $nisn) {
    $stmt = $conn->prepare("SELECT * FROM siswa WHERE nisn = ? LIMIT 1");
    if (!$stmt) return null;
    $stmt->bind_param("s", $nisn);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    return $data;
}

/**
 * Ambil pesan kelulusan aktif secara acak
 */
function getRandomPesanKelulusan($conn) {
    $default = 'Teruslah berjuang dan raih cita-citamu. Semoga sukses di masa depan!';
    $stmt = $conn->prepare("SELECT pesan FROM pesan_kelulusan WHERE is_active = 1 ORDER BY RAND() LIMIT 1");
    if (!$stmt) return $default;

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    $pesan = trim((string)($row['pesan'] ?? ''));
    return $pesan !== '' ? $pesan : $default;
}

/**
 * Cek apakah pengumuman sudah dibuka
 */
function isPengumumanDibuka($pengaturan) {
    if (!$pengaturan) return false;
    $waktuPengumuman = strtotime($pengaturan['tanggal_pengumuman']);
    $waktuSekarang = time();
    return $waktuSekarang >= $waktuPengumuman;
}
