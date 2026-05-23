<?php
/**
 * Admin Pengaturan
 */
session_start();
require_once __DIR__ . '/../config/koneksi.php';

const ADMIN_CODE = '154rRtsr40-98';

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirectAdmin($menu = 'murid') {
    header('Location: index.php?menu=' . urlencode($menu));
    exit;
}

function csrfToken() {
    if (empty($_SESSION['admin_csrf'])) {
        $_SESSION['admin_csrf'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['admin_csrf'];
}

function requireCsrf() {
    $token = $_POST['csrf'] ?? '';
    if (!$token || !hash_equals($_SESSION['admin_csrf'] ?? '', $token)) {
        $_SESSION['admin_flash'] = ['type' => 'error', 'message' => 'Sesi tidak valid. Silakan coba lagi.'];
        redirectAdmin($_GET['menu'] ?? 'murid');
    }
}

function setFlash($type, $message) {
    $_SESSION['admin_flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    $flash = $_SESSION['admin_flash'] ?? null;
    unset($_SESSION['admin_flash']);
    return $flash;
}

function ensurePengaturanNipColumn($conn) {
    $result = $conn->query("SHOW COLUMNS FROM pengaturan LIKE 'alamat_sekolah'");
    if (!$result || $result->num_rows === 0) {
        $conn->query("ALTER TABLE pengaturan ADD COLUMN alamat_sekolah VARCHAR(255) DEFAULT NULL AFTER nama_sekolah");
    }

    $result = $conn->query("SHOW COLUMNS FROM pengaturan LIKE 'nip_kepsek'");
    if ($result && $result->num_rows > 0) return;
    $conn->query("ALTER TABLE pengaturan ADD COLUMN nip_kepsek VARCHAR(50) DEFAULT NULL AFTER nama_kepsek");
}

function ensurePesanKelulusanTable($conn) {
    $conn->query(
        "CREATE TABLE IF NOT EXISTS pesan_kelulusan (
            id INT AUTO_INCREMENT PRIMARY KEY,
            pesan TEXT NOT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $result = $conn->query("SELECT COUNT(*) AS total FROM pesan_kelulusan");
    $total = $result ? (int)($result->fetch_assoc()['total'] ?? 0) : 0;
    if ($total > 0) return;

    $defaults = [
        'Teruslah berjuang dan raih cita-citamu. Semoga sukses di masa depan!',
        'Kelulusan ini adalah awal dari perjalanan baru. Tetap rendah hati dan terus belajar.',
        'Selamat atas pencapaianmu. Jadikan hari ini sebagai semangat untuk meraih impian berikutnya.',
        'Perjuanganmu hari ini membuktikan bahwa usaha tidak pernah mengkhianati hasil.',
        'Terima kasih sudah berjuang dengan baik. Semoga langkah berikutnya penuh keberhasilan.',
    ];

    $stmt = $conn->prepare("INSERT INTO pesan_kelulusan (pesan, is_active) VALUES (?, 1)");
    if (!$stmt) return;
    foreach ($defaults as $pesan) {
        $stmt->bind_param('s', $pesan);
        $stmt->execute();
    }
    $stmt->close();
}

function getActivePengaturan($conn) {
    $result = $conn->query("SELECT * FROM pengaturan WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
    if ($result && $row = $result->fetch_assoc()) return $row;

    $fallback = $conn->query("SELECT * FROM pengaturan ORDER BY id DESC LIMIT 1");
    if ($fallback && $row = $fallback->fetch_assoc()) return $row;

    return [
        'id' => '',
        'nama_sekolah' => 'SMP Negeri 6 Kendari',
        'alamat_sekolah' => '',
        'nama_kepsek' => '',
        'nip_kepsek' => '',
        'tahun_ajaran' => '',
        'tanggal_pengumuman' => date('Y-m-d H:i:s'),
        'sambutan' => '',
        'logo' => '',
        'foto_kepsek' => '',
        'is_active' => 1,
    ];
}

function getMuridCount($conn, $keyword = '') {
    if ($keyword !== '') {
        $like = '%' . $keyword . '%';
        $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM siswa WHERE nisn LIKE ? OR nama LIKE ? OR kelas LIKE ? OR jurusan LIKE ?");
        $stmt->bind_param('ssss', $like, $like, $like, $like);
    } else {
        $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM siswa");
    }

    if (!$stmt) return 0;
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)($row['total'] ?? 0);
}

function getMuridList($conn, $keyword = '', $limit = 10, $offset = 0) {
    if ($keyword !== '') {
        $like = '%' . $keyword . '%';
        $stmt = $conn->prepare("SELECT * FROM siswa WHERE nisn LIKE ? OR nama LIKE ? OR kelas LIKE ? OR jurusan LIKE ? ORDER BY nama ASC LIMIT ? OFFSET ?");
        $stmt->bind_param('ssssii', $like, $like, $like, $like, $limit, $offset);
    } else {
        $stmt = $conn->prepare("SELECT * FROM siswa ORDER BY nama ASC LIMIT ? OFFSET ?");
        $stmt->bind_param('ii', $limit, $offset);
    }

    if (!$stmt) return [];
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = [];
    while ($row = $result->fetch_assoc()) $rows[] = $row;
    $stmt->close();
    return $rows;
}

function getMuridById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM siswa WHERE id = ? LIMIT 1");
    if (!$stmt) return null;
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row;
}

function getPesanCount($conn) {
    $result = $conn->query("SELECT COUNT(*) AS total FROM pesan_kelulusan");
    $row = $result ? $result->fetch_assoc() : null;
    return (int)($row['total'] ?? 0);
}

function getPesanList($conn, $limit = 10, $offset = 0) {
    $stmt = $conn->prepare("SELECT * FROM pesan_kelulusan ORDER BY is_active DESC, updated_at DESC, id DESC LIMIT ? OFFSET ?");
    if (!$stmt) return [];
    $stmt->bind_param('ii', $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = [];
    while ($row = $result->fetch_assoc()) $rows[] = $row;
    $stmt->close();
    return $rows;
}

function getPesanById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM pesan_kelulusan WHERE id = ? LIMIT 1");
    if (!$stmt) return null;
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row;
}

function normalizeHeader($value) {
    $value = strtolower(trim((string)$value));
    $value = preg_replace('/[^a-z0-9]+/', '_', $value);
    return trim($value, '_');
}

function normalizeStatusKelulusan($value) {
    $value = strtoupper(trim((string)$value));
    $value = preg_replace('/\s+/', ' ', $value);
    return $value === 'TIDAK LULUS' ? 'TIDAK LULUS' : ($value === 'LULUS' ? 'LULUS' : '');
}

function columnIndexFromRef($cellRef) {
    preg_match('/[A-Z]+/', strtoupper((string)$cellRef), $matches);
    $letters = $matches[0] ?? 'A';
    $index = 0;
    for ($i = 0; $i < strlen($letters); $i++) {
        $index = ($index * 26) + (ord($letters[$i]) - 64);
    }
    return $index - 1;
}

function readXlsxRows($path) {
    if (!class_exists('ZipArchive')) {
        throw new RuntimeException('Import XLSX membutuhkan ekstensi PHP ZipArchive.');
    }

    $zip = new ZipArchive();
    if ($zip->open($path) !== true) {
        throw new RuntimeException('File XLSX tidak dapat dibuka.');
    }

    $sharedStrings = [];
    $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
    if ($sharedXml !== false) {
        $shared = simplexml_load_string($sharedXml);
        if ($shared) {
            foreach ($shared->si as $si) {
                $text = '';
                if (isset($si->t)) {
                    $text = (string)$si->t;
                } elseif (isset($si->r)) {
                    foreach ($si->r as $run) $text .= (string)$run->t;
                }
                $sharedStrings[] = $text;
            }
        }
    }

    $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
    $zip->close();
    if ($sheetXml === false) {
        throw new RuntimeException('Sheet pertama tidak ditemukan.');
    }

    $sheet = simplexml_load_string($sheetXml);
    if (!$sheet) {
        throw new RuntimeException('Sheet pertama tidak dapat dibaca.');
    }

    $rows = [];
    foreach ($sheet->sheetData->row as $rowNode) {
        $row = [];
        foreach ($rowNode->c as $cell) {
            $index = columnIndexFromRef((string)$cell['r']);
            $type = (string)$cell['t'];
            if ($type === 's') {
                $value = $sharedStrings[(int)$cell->v] ?? '';
            } elseif ($type === 'inlineStr') {
                $value = (string)($cell->is->t ?? '');
            } else {
                $value = (string)($cell->v ?? '');
            }
            $row[$index] = trim($value);
        }
        if ($row) {
            ksort($row);
            $rows[] = $row;
        }
    }

    return $rows;
}

function readCsvRows($path) {
    $rows = [];
    $handle = fopen($path, 'r');
    if (!$handle) throw new RuntimeException('File CSV tidak dapat dibuka.');
    while (($row = fgetcsv($handle, 0, ',')) !== false) {
        $rows[] = array_map('trim', $row);
    }
    fclose($handle);
    return $rows;
}

function importMuridRows($conn, $rows) {
    if (count($rows) < 2) {
        throw new RuntimeException('File import tidak memiliki data murid.');
    }

    $headers = array_map('normalizeHeader', $rows[0]);
    $aliases = [
        'nisn' => ['nisn'],
        'nama' => ['nama', 'nama_murid'],
        'kelas' => ['kelas'],
        'jurusan' => ['jurusan'],
        'status_kelulusan' => ['status_kelulusan', 'status'],
    ];

    $map = [];
    foreach ($aliases as $field => $names) {
        foreach ($names as $name) {
            $index = array_search($name, $headers, true);
            if ($index !== false) {
                $map[$field] = $index;
                break;
            }
        }
    }

    foreach (array_keys($aliases) as $required) {
        if (!isset($map[$required])) {
            throw new RuntimeException('Header wajib: nisn, nama, kelas, jurusan, status_kelulusan.');
        }
    }

    $stmt = $conn->prepare(
        "INSERT INTO siswa (nisn, nama, kelas, jurusan, status_kelulusan)
         VALUES (?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE nama = VALUES(nama), kelas = VALUES(kelas), jurusan = VALUES(jurusan), status_kelulusan = VALUES(status_kelulusan)"
    );
    if (!$stmt) throw new RuntimeException('Query import gagal disiapkan.');

    $success = 0;
    $skipped = 0;
    $errors = [];

    for ($i = 1; $i < count($rows); $i++) {
        $row = $rows[$i];
        if (!array_filter($row, fn($cell) => trim((string)$cell) !== '')) continue;

        $nisn = preg_replace('/\D/', '', (string)($row[$map['nisn']] ?? ''));
        if ($nisn !== '' && strlen($nisn) < 10) $nisn = str_pad($nisn, 10, '0', STR_PAD_LEFT);
        $nama = trim((string)($row[$map['nama']] ?? ''));
        $kelas = trim((string)($row[$map['kelas']] ?? ''));
        $jurusan = trim((string)($row[$map['jurusan']] ?? ''));
        $status = normalizeStatusKelulusan($row[$map['status_kelulusan']] ?? '');

        if (!preg_match('/^\d{10}$/', $nisn) || $nama === '' || $kelas === '' || $jurusan === '' || $status === '') {
            $skipped++;
            if (count($errors) < 5) $errors[] = 'Baris ' . ($i + 1) . ' dilewati karena data tidak valid.';
            continue;
        }

        $stmt->bind_param('sssss', $nisn, $nama, $kelas, $jurusan, $status);
        if ($stmt->execute()) {
            $success++;
        } else {
            $skipped++;
            if (count($errors) < 5) $errors[] = 'Baris ' . ($i + 1) . ': ' . $stmt->error;
        }
    }

    $stmt->close();
    return [$success, $skipped, $errors];
}

if (isset($_GET['download_template']) && !empty($_SESSION['admin_logged_in'])) {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="template_import_murid.csv"');
    echo "\xEF\xBB\xBF";
    echo "nisn,nama,kelas,jurusan,status_kelulusan\n";
    echo "0056789012,Budi Santoso,IX A,Umum,LULUS\n";
    echo "0056789013,Siti Aminah,IX B,Umum,TIDAK LULUS\n";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    if (hash_equals(ADMIN_CODE, trim($_POST['admin_code'] ?? ''))) {
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        csrfToken();
        redirectAdmin('murid');
    }

    $loginError = 'Kode admin tidak sesuai.';
}

if (isset($_GET['logout'])) {
    unset($_SESSION['admin_logged_in'], $_SESSION['admin_csrf']);
    redirectAdmin('murid');
}

$isLoggedIn = !empty($_SESSION['admin_logged_in']);
if ($isLoggedIn) {
    ensurePengaturanNipColumn($conn);
    ensurePesanKelulusanTable($conn);
}

if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'save_murid') {
        $id = (int)($_POST['id'] ?? 0);
        $nisn = trim($_POST['nisn'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $kelas = trim($_POST['kelas'] ?? '');
        $jurusan = trim($_POST['jurusan'] ?? '');
        $status = $_POST['status_kelulusan'] ?? '';

        if (!preg_match('/^\d{10}$/', $nisn)) {
            setFlash('error', 'NISN harus terdiri dari 10 digit angka.');
        } elseif ($nama === '' || $kelas === '' || $jurusan === '' || !in_array($status, ['LULUS', 'TIDAK LULUS'], true)) {
            setFlash('error', 'Lengkapi semua data murid dengan benar.');
        } elseif ($id > 0) {
            $stmt = $conn->prepare("UPDATE siswa SET nisn = ?, nama = ?, kelas = ?, jurusan = ?, status_kelulusan = ? WHERE id = ?");
            $stmt->bind_param('sssssi', $nisn, $nama, $kelas, $jurusan, $status, $id);
            setFlash($stmt->execute() ? 'success' : 'error', $stmt->errno === 1062 ? 'NISN sudah digunakan murid lain.' : ($stmt->error ?: 'Data murid berhasil diperbarui.'));
            $stmt->close();
        } else {
            $stmt = $conn->prepare("INSERT INTO siswa (nisn, nama, kelas, jurusan, status_kelulusan) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('sssss', $nisn, $nama, $kelas, $jurusan, $status);
            setFlash($stmt->execute() ? 'success' : 'error', $stmt->errno === 1062 ? 'NISN sudah digunakan murid lain.' : ($stmt->error ?: 'Data murid berhasil ditambahkan.'));
            $stmt->close();
        }
        redirectAdmin('murid');
    }

    if ($action === 'delete_murid') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM siswa WHERE id = ?");
        $stmt->bind_param('i', $id);
        setFlash($stmt->execute() ? 'success' : 'error', $stmt->error ?: 'Data murid berhasil dihapus.');
        $stmt->close();
        redirectAdmin('murid');
    }

    if ($action === 'save_pesan') {
        $id = (int)($_POST['id'] ?? 0);
        $pesan = trim($_POST['pesan'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($pesan === '') {
            setFlash('error', 'Isi pesan kelulusan tidak boleh kosong.');
        } elseif ($id > 0) {
            $stmt = $conn->prepare("UPDATE pesan_kelulusan SET pesan = ?, is_active = ? WHERE id = ?");
            $stmt->bind_param('sii', $pesan, $isActive, $id);
            setFlash($stmt->execute() ? 'success' : 'error', $stmt->error ?: 'Pesan berhasil diperbarui.');
            $stmt->close();
        } else {
            $stmt = $conn->prepare("INSERT INTO pesan_kelulusan (pesan, is_active) VALUES (?, ?)");
            $stmt->bind_param('si', $pesan, $isActive);
            setFlash($stmt->execute() ? 'success' : 'error', $stmt->error ?: 'Pesan berhasil ditambahkan.');
            $stmt->close();
        }
        redirectAdmin('pesan');
    }

    if ($action === 'delete_pesan') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM pesan_kelulusan WHERE id = ?");
        $stmt->bind_param('i', $id);
        setFlash($stmt->execute() ? 'success' : 'error', $stmt->error ?: 'Pesan berhasil dihapus.');
        $stmt->close();
        redirectAdmin('pesan');
    }

    if ($action === 'import_murid') {
        try {
            if (empty($_FILES['file_import']) || $_FILES['file_import']['error'] !== UPLOAD_ERR_OK) {
                throw new RuntimeException('Pilih file import terlebih dahulu.');
            }

            $file = $_FILES['file_import'];
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, ['xlsx', 'csv'], true)) {
                throw new RuntimeException('Format file harus .xlsx atau .csv. Untuk .xls lama, simpan ulang sebagai .xlsx.');
            }

            $rows = $extension === 'xlsx' ? readXlsxRows($file['tmp_name']) : readCsvRows($file['tmp_name']);
            [$success, $skipped, $errors] = importMuridRows($conn, $rows);
            $message = "Import selesai. {$success} data berhasil diproses";
            if ($skipped > 0) $message .= ", {$skipped} baris dilewati";
            if ($errors) $message .= '. ' . implode(' ', $errors);
            setFlash($success > 0 ? 'success' : 'error', $message . '.');
        } catch (Throwable $e) {
            setFlash('error', $e->getMessage());
        }
        redirectAdmin('murid');
    }

    if ($action === 'save_pengaturan') {
        $id = (int)($_POST['id'] ?? 0);
        $namaSekolah = trim($_POST['nama_sekolah'] ?? '');
        $alamatSekolah = trim($_POST['alamat_sekolah'] ?? '');
        $namaKepsek = trim($_POST['nama_kepsek'] ?? '');
        $nipKepsek = trim($_POST['nip_kepsek'] ?? '');
        $tahunAjaran = trim($_POST['tahun_ajaran'] ?? '');
        $tanggalPengumuman = trim($_POST['tanggal_pengumuman'] ?? '');
        $sambutan = trim($_POST['sambutan'] ?? '');
        $logo = trim($_POST['logo'] ?? '');
        $fotoKepsek = trim($_POST['foto_kepsek'] ?? '');
        $tanggalDb = str_replace('T', ' ', $tanggalPengumuman);
        if (strlen($tanggalDb) === 16) $tanggalDb .= ':00';

        if ($namaSekolah === '' || $namaKepsek === '' || $tahunAjaran === '' || strtotime($tanggalDb) === false) {
            setFlash('error', 'Lengkapi pengaturan website dengan benar.');
            redirectAdmin('pengaturan');
        }

        $conn->query("UPDATE pengaturan SET is_active = 0");

        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE pengaturan SET nama_sekolah = ?, alamat_sekolah = ?, nama_kepsek = ?, nip_kepsek = ?, tahun_ajaran = ?, tanggal_pengumuman = ?, sambutan = ?, logo = ?, foto_kepsek = ?, is_active = 1 WHERE id = ?");
            $stmt->bind_param('sssssssssi', $namaSekolah, $alamatSekolah, $namaKepsek, $nipKepsek, $tahunAjaran, $tanggalDb, $sambutan, $logo, $fotoKepsek, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO pengaturan (nama_sekolah, alamat_sekolah, nama_kepsek, nip_kepsek, tahun_ajaran, tanggal_pengumuman, sambutan, logo, foto_kepsek, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
            $stmt->bind_param('sssssssss', $namaSekolah, $alamatSekolah, $namaKepsek, $nipKepsek, $tahunAjaran, $tanggalDb, $sambutan, $logo, $fotoKepsek);
        }

        setFlash($stmt->execute() ? 'success' : 'error', $stmt->error ?: 'Pengaturan berhasil disimpan.');
        $stmt->close();
        redirectAdmin('pengaturan');
    }
}

$menu = $_GET['menu'] ?? 'murid';
if (!in_array($menu, ['murid', 'pengaturan', 'pesan'], true)) $menu = 'murid';

$keyword = trim($_GET['q'] ?? '');
$perPage = 10;
$currentPage = max(1, (int)($_GET['page'] ?? 1));
$totalMurid = $isLoggedIn && $menu === 'murid' ? getMuridCount($conn, $keyword) : 0;
$totalPesan = $isLoggedIn && $menu === 'pesan' ? getPesanCount($conn) : 0;
$totalPages = max(1, (int)ceil(($menu === 'pesan' ? $totalPesan : $totalMurid) / $perPage));
if ($currentPage > $totalPages) $currentPage = $totalPages;
$offset = ($currentPage - 1) * $perPage;
$editMurid = $isLoggedIn && $menu === 'murid' && isset($_GET['edit']) ? getMuridById($conn, (int)$_GET['edit']) : null;
$editPesan = $isLoggedIn && $menu === 'pesan' && isset($_GET['edit']) ? getPesanById($conn, (int)$_GET['edit']) : null;
$muridList = $isLoggedIn && $menu === 'murid' ? getMuridList($conn, $keyword, $perPage, $offset) : [];
$pesanList = $isLoggedIn && $menu === 'pesan' ? getPesanList($conn, $perPage, $offset) : [];
$pengaturan = $isLoggedIn ? getActivePengaturan($conn) : null;
$flash = getFlash();
$csrf = csrfToken();
$startMurid = $totalMurid > 0 ? $offset + 1 : 0;
$endMurid = min($offset + count($muridList), $totalMurid);
$startPesan = $totalPesan > 0 ? $offset + 1 : 0;
$endPesan = min($offset + count($pesanList), $totalPesan);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>Admin Pengaturan | SMP Negeri 6 Kendari</title>
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        window.tailwind = window.tailwind || {};
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0A84FF',
                        'primary-dark': '#0066CC',
                        secondary: '#4DA3FF',
                        'bg-main': '#EEF5FF',
                        success: '#22C55E',
                        'success-light': '#DCFCE7',
                        danger: '#FF5A5A',
                        'danger-light': '#FEE2E2',
                        'text-dark': '#1E293B',
                        'text-muted': '#64748B',
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'],
                    },
                    boxShadow: {
                        'card': '0 4px 24px -2px rgba(10, 132, 255, 0.10)',
                        'card-lg': '0 8px 40px -4px rgba(10, 132, 255, 0.15)',
                        'btn': '0 4px 14px -2px rgba(10, 132, 255, 0.4)',
                    }
                }
            }
        }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="font-sans bg-bg-main text-text-dark min-h-screen antialiased">
<?php if (!$isLoggedIn): ?>
    <main class="min-h-screen flex items-center justify-center p-5">
        <section class="w-full max-w-md bg-white rounded-3xl p-7 shadow-card-lg border border-blue-100">
            <div class="text-center mb-7">
                <img src="/assets/images/logo_sekolah.png" alt="Logo Sekolah" class="w-16 h-16 object-contain mx-auto mb-4">
                <h1 class="text-2xl font-extrabold text-text-dark">Admin Pengaturan</h1>
                <p class="text-sm text-text-muted mt-2">Masukkan kode admin untuk mengelola data.</p>
            </div>
            <?php if (!empty($loginError)): ?>
            <div class="notice-card notice-danger rounded-2xl p-4 mb-5 text-sm text-danger font-semibold">
                <?= h($loginError) ?>
            </div>
            <?php endif; ?>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="login">
                <div>
                    <label class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-2">Kode Admin</label>
                    <input type="password" name="admin_code" class="input-modern field-3d w-full px-4 py-4 rounded-2xl border border-blue-100 bg-white text-base font-semibold focus:outline-none" placeholder="Masukkan kode admin" required autofocus>
                </div>
                <button type="submit" class="btn-3d w-full rounded-2xl py-4 text-white font-bold">Masuk Admin</button>
            </form>
        </section>
    </main>
<?php else: ?>
    <div class="min-h-screen p-4 md:p-8">
        <div class="max-w-6xl mx-auto">
            <header class="bg-white rounded-3xl p-5 md:p-6 shadow-card-lg border border-blue-100 mb-5">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <img src="/assets/images/logo_sekolah.png" alt="Logo Sekolah" class="w-14 h-14 object-contain">
                        <div>
                            <p class="text-sm text-primary font-bold">SMP NEGERI 6 KENDARI</p>
                            <h1 class="text-2xl font-extrabold text-text-dark">Admin Pengaturan</h1>
                        </div>
                    </div>
                    <a href="index.php?logout=1" class="btn-outline-3d inline-flex items-center justify-center rounded-2xl border border-blue-200 px-5 py-3 text-sm font-bold text-primary bg-white">Keluar</a>
                </div>
            </header>

            <?php if ($flash): ?>
            <div class="rounded-2xl p-4 mb-5 text-sm font-semibold <?= $flash['type'] === 'success' ? 'notice-card notice-success text-success' : 'notice-card notice-danger text-danger' ?>">
                <?= h($flash['message']) ?>
            </div>
            <?php endif; ?>

            <nav class="admin-tabs mb-5 grid grid-cols-3 gap-2">
                <a href="index.php?menu=murid" class="admin-tab <?= $menu === 'murid' ? 'is-active' : '' ?>">Data Murid</a>
                <a href="index.php?menu=pengaturan" class="admin-tab <?= $menu === 'pengaturan' ? 'is-active' : '' ?>">Pengaturan</a>
                <a href="index.php?menu=pesan" class="admin-tab <?= $menu === 'pesan' ? 'is-active' : '' ?>">Pesan</a>
            </nav>

            <?php if ($menu === 'murid'): ?>
            <section class="grid lg:grid-cols-[380px,1fr] gap-5">
                <div class="space-y-5">
                <form method="POST" class="bg-white rounded-3xl p-5 shadow-card-lg border border-blue-100 h-fit">
                    <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
                    <input type="hidden" name="action" value="save_murid">
                    <input type="hidden" name="id" value="<?= h($editMurid['id'] ?? '') ?>">
                    <div class="mb-5">
                        <h2 class="text-lg font-extrabold text-text-dark"><?= $editMurid ? 'Edit Murid' : 'Tambah Murid' ?></h2>
                        <p class="text-xs text-text-muted mt-1">Kelola NISN, kelas, dan status kelulusan.</p>
                    </div>
                    <div class="space-y-4">
                        <label class="block">
                            <span class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-2">NISN</span>
                            <input name="nisn" value="<?= h($editMurid['nisn'] ?? '') ?>" maxlength="10" inputmode="numeric" class="w-full rounded-2xl border border-blue-100 px-4 py-3.5 font-semibold focus:outline-none focus:border-primary" required>
                        </label>
                        <label class="block">
                            <span class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-2">Nama Murid</span>
                            <input name="nama" value="<?= h($editMurid['nama'] ?? '') ?>" class="w-full rounded-2xl border border-blue-100 px-4 py-3.5 font-semibold focus:outline-none focus:border-primary" required>
                        </label>
                        <label class="block">
                            <span class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-2">Kelas</span>
                            <input name="kelas" value="<?= h($editMurid['kelas'] ?? '') ?>" class="w-full rounded-2xl border border-blue-100 px-4 py-3.5 font-semibold focus:outline-none focus:border-primary" required>
                        </label>
                        <label class="block">
                            <span class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-2">Jurusan</span>
                            <input name="jurusan" value="<?= h($editMurid['jurusan'] ?? '') ?>" class="w-full rounded-2xl border border-blue-100 px-4 py-3.5 font-semibold focus:outline-none focus:border-primary" required>
                        </label>
                        <label class="block">
                            <span class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-2">Status Kelulusan</span>
                            <select name="status_kelulusan" class="w-full rounded-2xl border border-blue-100 px-4 py-3.5 font-semibold focus:outline-none focus:border-primary" required>
                                <?php $selectedStatus = $editMurid['status_kelulusan'] ?? 'LULUS'; ?>
                                <option value="LULUS" <?= $selectedStatus === 'LULUS' ? 'selected' : '' ?>>LULUS</option>
                                <option value="TIDAK LULUS" <?= $selectedStatus === 'TIDAK LULUS' ? 'selected' : '' ?>>TIDAK LULUS</option>
                            </select>
                        </label>
                    </div>
                    <div class="flex gap-3 mt-5">
                        <button type="submit" class="btn-3d flex-1 rounded-2xl py-3.5 text-white font-bold"><?= $editMurid ? 'Simpan' : 'Tambah' ?></button>
                        <?php if ($editMurid): ?>
                        <a href="index.php?menu=murid" class="btn-outline-3d rounded-2xl border border-blue-200 px-5 py-3.5 text-sm font-bold text-text-muted bg-white">Batal</a>
                        <?php endif; ?>
                    </div>
                </form>

                <form method="POST" enctype="multipart/form-data" class="bg-white rounded-3xl p-5 shadow-card-lg border border-blue-100 h-fit">
                    <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
                    <input type="hidden" name="action" value="import_murid">
                    <div class="mb-4">
                        <h2 class="text-lg font-extrabold text-text-dark">Import Data Excel</h2>
                        <p class="text-xs text-text-muted mt-1">Gunakan kolom: nisn, nama, kelas, jurusan, status_kelulusan.</p>
                    </div>
                    <label class="block">
                        <span class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-2">File Excel / CSV</span>
                        <input type="file" name="file_import" accept=".xlsx,.csv" class="w-full rounded-2xl border border-blue-100 bg-blue-50/60 px-4 py-3 text-sm font-semibold text-text-dark file:mr-3 file:rounded-xl file:border-0 file:bg-primary file:px-3 file:py-2 file:text-white file:font-bold" required>
                    </label>
                    <div class="mt-4 flex flex-col sm:flex-row gap-3">
                        <button type="submit" class="btn-3d flex-1 rounded-2xl py-3.5 text-white font-bold">Import Data</button>
                        <a href="index.php?download_template=1" class="btn-outline-3d inline-flex items-center justify-center rounded-2xl border border-blue-200 bg-white px-4 py-3.5 text-sm font-bold text-primary">Unduh Template</a>
                    </div>
                    <p class="text-[11px] leading-relaxed text-text-muted mt-3">Format `.xlsx` membaca sheet pertama. File `.xls` lama perlu disimpan ulang menjadi `.xlsx`.</p>
                </form>
                </div>

                <div class="bg-white rounded-3xl p-5 shadow-card-lg border border-blue-100">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-5">
                        <div>
                            <h2 class="text-lg font-extrabold text-text-dark">Data Murid</h2>
                            <p class="text-xs text-text-muted mt-1">Menampilkan <?= $startMurid ?>-<?= $endMurid ?> dari <?= $totalMurid ?> data</p>
                        </div>
                        <form method="GET" class="flex gap-2">
                            <input type="hidden" name="menu" value="murid">
                            <input name="q" value="<?= h($keyword) ?>" class="rounded-2xl border border-blue-100 px-4 py-2.5 text-sm focus:outline-none focus:border-primary" placeholder="Cari murid...">
                            <button class="rounded-2xl bg-primary px-4 py-2.5 text-sm font-bold text-white">Cari</button>
                        </form>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-text-muted border-b border-blue-100">
                                    <th class="py-3 pr-4">Nama Murid</th>
                                    <th class="py-3 pr-4">NISN</th>
                                    <th class="py-3 pr-4">Kelas</th>
                                    <th class="py-3 pr-4">Jurusan</th>
                                    <th class="py-3 pr-4">Status</th>
                                    <th class="py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($muridList as $murid): ?>
                                <tr class="border-b border-blue-50">
                                    <td class="py-3 pr-4 font-bold text-text-dark"><?= h($murid['nama']) ?></td>
                                    <td class="py-3 pr-4"><?= h($murid['nisn']) ?></td>
                                    <td class="py-3 pr-4"><?= h($murid['kelas']) ?></td>
                                    <td class="py-3 pr-4"><?= h($murid['jurusan']) ?></td>
                                    <td class="py-3 pr-4">
                                        <span class="status-pill <?= $murid['status_kelulusan'] === 'LULUS' ? 'status-success' : 'status-danger' ?> px-3 py-1 rounded-lg text-[11px] font-bold"><?= h($murid['status_kelulusan']) ?></span>
                                    </td>
                                    <td class="py-3 text-right whitespace-nowrap">
                                        <a href="index.php?menu=murid&edit=<?= (int)$murid['id'] ?>" class="inline-flex rounded-xl bg-blue-50 px-3 py-2 text-xs font-bold text-primary">Edit</a>
                                        <form method="POST" class="inline" onsubmit="return confirm('Hapus data murid ini?')">
                                            <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
                                            <input type="hidden" name="action" value="delete_murid">
                                            <input type="hidden" name="id" value="<?= (int)$murid['id'] ?>">
                                            <button class="rounded-xl bg-red-50 px-3 py-2 text-xs font-bold text-danger">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (!$muridList): ?>
                                <tr><td colspan="6" class="py-8 text-center text-text-muted">Belum ada data murid.</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ($totalPages > 1): ?>
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mt-5 pt-4 border-t border-blue-100">
                        <p class="text-xs text-text-muted font-semibold">Halaman <?= $currentPage ?> dari <?= $totalPages ?></p>
                        <div class="flex flex-wrap gap-2">
                            <?php
                                $baseParams = ['menu' => 'murid'];
                                if ($keyword !== '') $baseParams['q'] = $keyword;
                            ?>
                            <a class="pagination-btn <?= $currentPage <= 1 ? 'is-disabled' : '' ?>" href="index.php?<?= h(http_build_query($baseParams + ['page' => max(1, $currentPage - 1)])) ?>">Sebelumnya</a>
                            <?php for ($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++): ?>
                                <?php if ($pageNumber === 1 || $pageNumber === $totalPages || abs($pageNumber - $currentPage) <= 1): ?>
                                <a class="pagination-btn <?= $pageNumber === $currentPage ? 'is-active' : '' ?>" href="index.php?<?= h(http_build_query($baseParams + ['page' => $pageNumber])) ?>"><?= $pageNumber ?></a>
                                <?php elseif (abs($pageNumber - $currentPage) === 2): ?>
                                <span class="pagination-ellipsis">...</span>
                                <?php endif; ?>
                            <?php endfor; ?>
                            <a class="pagination-btn <?= $currentPage >= $totalPages ? 'is-disabled' : '' ?>" href="index.php?<?= h(http_build_query($baseParams + ['page' => min($totalPages, $currentPage + 1)])) ?>">Berikutnya</a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </section>
            <?php elseif ($menu === 'pengaturan'): ?>
            <section class="bg-white rounded-3xl p-5 md:p-6 shadow-card-lg border border-blue-100">
                <div class="mb-5">
                    <h2 class="text-lg font-extrabold text-text-dark">Pengaturan Website</h2>
                    <p class="text-xs text-text-muted mt-1">Atur identitas sekolah, jadwal pengumuman, dan sambutan.</p>
                </div>
                <form method="POST" class="grid md:grid-cols-2 gap-4">
                    <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
                    <input type="hidden" name="action" value="save_pengaturan">
                    <input type="hidden" name="id" value="<?= h($pengaturan['id'] ?? '') ?>">
                    <label class="block">
                        <span class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-2">Nama Sekolah</span>
                        <input name="nama_sekolah" value="<?= h($pengaturan['nama_sekolah'] ?? '') ?>" class="w-full rounded-2xl border border-blue-100 px-4 py-3.5 font-semibold focus:outline-none focus:border-primary" required>
                    </label>
                    <label class="block">
                        <span class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-2">Alamat Sekolah</span>
                        <input name="alamat_sekolah" value="<?= h($pengaturan['alamat_sekolah'] ?? '') ?>" class="w-full rounded-2xl border border-blue-100 px-4 py-3.5 font-semibold focus:outline-none focus:border-primary" placeholder="Jl. R.A Kartini No. 1 Kendari">
                    </label>
                    <label class="block">
                        <span class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-2">Nama Kepala Sekolah</span>
                        <input name="nama_kepsek" value="<?= h($pengaturan['nama_kepsek'] ?? '') ?>" class="w-full rounded-2xl border border-blue-100 px-4 py-3.5 font-semibold focus:outline-none focus:border-primary" required>
                    </label>
                    <label class="block">
                        <span class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-2">NIP Kepala Sekolah</span>
                        <input name="nip_kepsek" value="<?= h($pengaturan['nip_kepsek'] ?? '') ?>" class="w-full rounded-2xl border border-blue-100 px-4 py-3.5 font-semibold focus:outline-none focus:border-primary" placeholder="196801011990031001">
                    </label>
                    <label class="block">
                        <span class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-2">Tahun Ajaran</span>
                        <input name="tahun_ajaran" value="<?= h($pengaturan['tahun_ajaran'] ?? '') ?>" class="w-full rounded-2xl border border-blue-100 px-4 py-3.5 font-semibold focus:outline-none focus:border-primary" placeholder="2025/2026" required>
                    </label>
                    <label class="block">
                        <span class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-2">Tanggal Pengumuman</span>
                        <input type="datetime-local" name="tanggal_pengumuman" value="<?= h(date('Y-m-d\TH:i', strtotime($pengaturan['tanggal_pengumuman'] ?? 'now'))) ?>" class="w-full rounded-2xl border border-blue-100 px-4 py-3.5 font-semibold focus:outline-none focus:border-primary" required>
                    </label>
                    <label class="block">
                        <span class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-2">Path Logo</span>
                        <input name="logo" value="<?= h($pengaturan['logo'] ?? '') ?>" class="w-full rounded-2xl border border-blue-100 px-4 py-3.5 font-semibold focus:outline-none focus:border-primary" placeholder="assets/images/logo_sekolah.png">
                    </label>
                    <label class="block">
                        <span class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-2">Path Foto Kepala Sekolah</span>
                        <input name="foto_kepsek" value="<?= h($pengaturan['foto_kepsek'] ?? '') ?>" class="w-full rounded-2xl border border-blue-100 px-4 py-3.5 font-semibold focus:outline-none focus:border-primary" placeholder="assets/images/kepala_sekolah.png">
                    </label>
                    <label class="block md:col-span-2">
                        <span class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-2">Sambutan</span>
                        <textarea name="sambutan" rows="5" class="w-full rounded-2xl border border-blue-100 px-4 py-3.5 font-semibold focus:outline-none focus:border-primary" required><?= h($pengaturan['sambutan'] ?? '') ?></textarea>
                    </label>
                    <div class="md:col-span-2">
                        <button type="submit" class="btn-3d rounded-2xl px-6 py-3.5 text-white font-bold">Simpan Pengaturan</button>
                    </div>
                </form>
            </section>
            <?php elseif ($menu === 'pesan'): ?>
            <section class="grid lg:grid-cols-[380px,1fr] gap-5">
                <form method="POST" class="bg-white rounded-3xl p-5 shadow-card-lg border border-blue-100 h-fit">
                    <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
                    <input type="hidden" name="action" value="save_pesan">
                    <input type="hidden" name="id" value="<?= h($editPesan['id'] ?? '') ?>">
                    <div class="mb-5">
                        <h2 class="text-lg font-extrabold text-text-dark"><?= $editPesan ? 'Edit Pesan' : 'Tambah Pesan' ?></h2>
                        <p class="text-xs text-text-muted mt-1">Pesan aktif akan diacak pada halaman hasil LULUS.</p>
                    </div>
                    <label class="block">
                        <span class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-2">Isi Pesan</span>
                        <textarea name="pesan" rows="6" class="w-full rounded-2xl border border-blue-100 px-4 py-3.5 font-semibold focus:outline-none focus:border-primary" required><?= h($editPesan['pesan'] ?? '') ?></textarea>
                    </label>
                    <label class="mt-4 flex items-center gap-3 rounded-2xl border border-blue-100 bg-blue-50/60 px-4 py-3">
                        <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-blue-200 text-primary focus:ring-primary" <?= ((int)($editPesan['is_active'] ?? 1) === 1) ? 'checked' : '' ?>>
                        <span class="text-sm font-bold text-text-dark">Aktif</span>
                    </label>
                    <div class="flex gap-3 mt-5">
                        <button type="submit" class="btn-3d flex-1 rounded-2xl py-3.5 text-white font-bold"><?= $editPesan ? 'Simpan' : 'Tambah' ?></button>
                        <?php if ($editPesan): ?>
                        <a href="index.php?menu=pesan" class="btn-outline-3d rounded-2xl border border-blue-200 px-5 py-3.5 text-sm font-bold text-text-muted bg-white">Batal</a>
                        <?php endif; ?>
                    </div>
                </form>

                <div class="bg-white rounded-3xl p-5 shadow-card-lg border border-blue-100">
                    <div class="mb-5">
                        <h2 class="text-lg font-extrabold text-text-dark">Data Pesan</h2>
                        <p class="text-xs text-text-muted mt-1">Menampilkan <?= $startPesan ?>-<?= $endPesan ?> dari <?= $totalPesan ?> pesan</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-text-muted border-b border-blue-100">
                                    <th class="py-3 pr-4">Pesan</th>
                                    <th class="py-3 pr-4">Status</th>
                                    <th class="py-3 pr-4">Diperbarui</th>
                                    <th class="py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($pesanList as $pesanRow): ?>
                                <tr class="border-b border-blue-50 align-top">
                                    <td class="py-3 pr-4 font-semibold text-text-dark min-w-[320px]"><?= h($pesanRow['pesan']) ?></td>
                                    <td class="py-3 pr-4 whitespace-nowrap">
                                        <?php if ((int)$pesanRow['is_active'] === 1): ?>
                                        <span class="status-pill status-success px-3 py-1 rounded-lg text-[11px] font-bold">AKTIF</span>
                                        <?php else: ?>
                                        <span class="status-pill status-danger px-3 py-1 rounded-lg text-[11px] font-bold">NONAKTIF</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 pr-4 whitespace-nowrap text-text-muted"><?= h(date('d/m/Y H:i', strtotime($pesanRow['updated_at'] ?? 'now'))) ?></td>
                                    <td class="py-3 text-right whitespace-nowrap">
                                        <a href="index.php?menu=pesan&edit=<?= (int)$pesanRow['id'] ?>" class="inline-flex rounded-xl bg-blue-50 px-3 py-2 text-xs font-bold text-primary">Edit</a>
                                        <form method="POST" class="inline" onsubmit="return confirm('Hapus pesan ini?')">
                                            <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
                                            <input type="hidden" name="action" value="delete_pesan">
                                            <input type="hidden" name="id" value="<?= (int)$pesanRow['id'] ?>">
                                            <button class="rounded-xl bg-red-50 px-3 py-2 text-xs font-bold text-danger">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (!$pesanList): ?>
                                <tr><td colspan="4" class="py-8 text-center text-text-muted">Belum ada pesan kelulusan.</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ($totalPages > 1): ?>
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mt-5 pt-4 border-t border-blue-100">
                        <p class="text-xs text-text-muted font-semibold">Halaman <?= $currentPage ?> dari <?= $totalPages ?></p>
                        <div class="flex flex-wrap gap-2">
                            <?php $baseParams = ['menu' => 'pesan']; ?>
                            <a class="pagination-btn <?= $currentPage <= 1 ? 'is-disabled' : '' ?>" href="index.php?<?= h(http_build_query($baseParams + ['page' => max(1, $currentPage - 1)])) ?>">Sebelumnya</a>
                            <?php for ($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++): ?>
                                <?php if ($pageNumber === 1 || $pageNumber === $totalPages || abs($pageNumber - $currentPage) <= 1): ?>
                                <a class="pagination-btn <?= $pageNumber === $currentPage ? 'is-active' : '' ?>" href="index.php?<?= h(http_build_query($baseParams + ['page' => $pageNumber])) ?>"><?= $pageNumber ?></a>
                                <?php elseif (abs($pageNumber - $currentPage) === 2): ?>
                                <span class="pagination-ellipsis">...</span>
                                <?php endif; ?>
                            <?php endfor; ?>
                            <a class="pagination-btn <?= $currentPage >= $totalPages ? 'is-disabled' : '' ?>" href="index.php?<?= h(http_build_query($baseParams + ['page' => min($totalPages, $currentPage + 1)])) ?>">Berikutnya</a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </section>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
</body>
</html>
