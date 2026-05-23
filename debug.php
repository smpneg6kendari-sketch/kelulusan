<?php
// FILE INI UNTUK DEBUG SAJA — HAPUS SETELAH SELESAI!

mysqli_report(MYSQLI_REPORT_OFF);

$host = 'localhost';
$user = 'afifeduc_kelulusan';
$pass = '30041982aA@';
$db   = 'afifeduc_kelulusan';

echo "<h2>MySQL Debug Info</h2>";
echo "<b>Host:</b> $host<br>";
echo "<b>User:</b> $user<br>";
echo "<b>DB:</b> $db<br><br>";

$conn = @new mysqli($host, $user, $pass, $db);

if ($conn->connect_errno) {
    echo "<b style='color:red'>❌ GAGAL KONEKSI</b><br>";
    echo "Error No: " . $conn->connect_errno . "<br>";
    echo "Error: " . $conn->connect_error . "<br><br>";
    echo "<hr><b>Kemungkinan penyebab:</b><ul>";
    if ($conn->connect_errno == 1045) {
        echo "<li>User/Password salah atau user belum di-assign ke database</li>";
    } elseif ($conn->connect_errno == 1044) {
        echo "<li>User tidak punya akses ke database ini</li>";
    } elseif ($conn->connect_errno == 1049) {
        echo "<li>Database tidak ditemukan — nama database salah</li>";
    } elseif ($conn->connect_errno == 2002) {
        echo "<li>Host tidak bisa dihubungi</li>";
    }
    echo "</ul>";
} else {
    echo "<b style='color:green'>✅ KONEKSI BERHASIL!</b><br>";
    echo "Server: " . $conn->server_info . "<br>";
    
    // Cek tabel
    $res = $conn->query("SHOW TABLES");
    echo "<br><b>Tabel yang ada:</b><ul>";
    while ($row = $res->fetch_row()) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
    $conn->close();
}
?>
