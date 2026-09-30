<?php
$conn = mysqli_connect(
    "sql300.infinityfree.com",
    "if0_41935051",
    "juniorPGD123",
    "if0_41935051_absensi_elite"
);

// Tambahkan ini:
if(!$conn){
    sleep(1); // tunggu 1 detik
    $conn = mysqli_connect(
        "sql300.infinityfree.com",
        "if0_41935051",
        "juniorPGD123",
        "if0_41935051_absensi_elite"
    );
}

if(!$conn) die("Koneksi gagal, coba refresh halaman.");