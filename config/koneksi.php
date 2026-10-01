<?php
$host = getenv('DB_HOST') ?: "sql300.infinityfree.com";
$user = getenv('DB_USERNAME') ?: "if0_41935051";
$pass = getenv('DB_PASSWORD') ?: "juniorPGD123";
$db   = getenv('DB_DATABASE') ?: "if0_41935051_absensi_elite";
$port = (int)(getenv('DB_PORT') ?: 3306);

$conn = mysqli_init();

// TiDB Cloud / Aiven memerlukan koneksi SSL (SSL_FLAG)
if (defined('MYSQLI_CLIENT_SSL')) {
    mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
    mysqli_real_connect($conn, $host, $user, $pass, $db, $port, NULL, MYSQLI_CLIENT_SSL);
} else {
    mysqli_real_connect($conn, $host, $user, $pass, $db, $port);
}

if (mysqli_connect_errno()) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>
