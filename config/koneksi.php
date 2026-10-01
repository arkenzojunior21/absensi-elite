<?php
$host = getenv('DB_HOST') ?: "sql300.infinityfree.com";
$user = getenv('DB_USERNAME') ?: "if0_41935051";
$pass = getenv('DB_PASSWORD') ?: "juniorPGD123";
$db   = getenv('DB_DATABASE') ?: "if0_41935051_absensi_elite";
$port = getenv('DB_PORT') ?: 3306;

$conn = mysqli_connect($host, $user, $pass, $db, (int)$port);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>
