-- ========================================
-- Update Database: afifeduc_kelulusan
-- Website Pengumuman Kelulusan Sekolah
-- Jalankan pada database yang tabelnya sudah ada.
-- ========================================

USE afifeduc_kelulusan;

-- Tambah kolom alamat sekolah jika belum ada.
SET @column_exists := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'pengaturan'
      AND COLUMN_NAME = 'alamat_sekolah'
);

SET @sql := IF(
    @column_exists = 0,
    'ALTER TABLE pengaturan ADD COLUMN alamat_sekolah VARCHAR(255) DEFAULT NULL AFTER nama_sekolah',
    'SELECT "Kolom alamat_sekolah sudah ada" AS info'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Tambah kolom NIP kepala sekolah jika belum ada.
SET @column_exists := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'pengaturan'
      AND COLUMN_NAME = 'nip_kepsek'
);

SET @sql := IF(
    @column_exists = 0,
    'ALTER TABLE pengaturan ADD COLUMN nip_kepsek VARCHAR(50) DEFAULT NULL AFTER nama_kepsek',
    'SELECT "Kolom nip_kepsek sudah ada" AS info'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Isi NIP default jika masih kosong. Silakan sesuaikan dari halaman admin Pengaturan.
UPDATE pengaturan
SET nip_kepsek = '196801011990031001'
WHERE is_active = 1
  AND (nip_kepsek IS NULL OR nip_kepsek = '');

-- Isi alamat default jika masih kosong. Silakan sesuaikan dari halaman admin Pengaturan.
UPDATE pengaturan
SET alamat_sekolah = 'Jl. R.A Kartini No. 1 Kendari'
WHERE is_active = 1
  AND (alamat_sekolah IS NULL OR alamat_sekolah = '');

-- Update istilah sambutan default lama dari "siswa-siswi" menjadi "murid".
UPDATE pengaturan
SET sambutan = REPLACE(sambutan, 'siswa-siswi', 'murid')
WHERE sambutan LIKE '%siswa-siswi%';

-- Tambah tabel pesan kelulusan untuk quote acak pada halaman hasil LULUS.
CREATE TABLE IF NOT EXISTS pesan_kelulusan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pesan TEXT NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Isi pesan awal hanya jika tabel masih kosong. Silakan kelola dari halaman admin tab Pesan.
SET @pesan_count := (SELECT COUNT(*) FROM pesan_kelulusan);

INSERT INTO pesan_kelulusan (pesan, is_active)
SELECT seed.pesan, seed.is_active
FROM (
    SELECT 'Teruslah berjuang dan raih cita-citamu. Semoga sukses di masa depan!' AS pesan, 1 AS is_active
    UNION ALL SELECT 'Kelulusan ini adalah awal dari perjalanan baru. Tetap rendah hati dan terus belajar.', 1
    UNION ALL SELECT 'Selamat atas pencapaianmu. Jadikan hari ini sebagai semangat untuk meraih impian berikutnya.', 1
    UNION ALL SELECT 'Perjuanganmu hari ini membuktikan bahwa usaha tidak pernah mengkhianati hasil.', 1
    UNION ALL SELECT 'Terima kasih sudah berjuang dengan baik. Semoga langkah berikutnya penuh keberhasilan.', 1
) AS seed
WHERE @pesan_count = 0;
