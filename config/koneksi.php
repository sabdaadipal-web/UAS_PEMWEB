<?php
// Konfigurasi database
$host = "localhost";
$user = "root";       
$pass = "";           
$db   = "db_ecafe"; 
$port = "8111";  

// Membuat koneksi
$koneksi = mysqli_connect($host, $user, $pass, $db, $port);

// Cek koneksi
if (mysqli_connect_errno()) {
    // Jika gagal
    echo "Gagal terhubung ke database: " . mysqli_connect_error();
    exit();
} else {
    // Jika berhasil
    //echo "<div class='alert alert-success'>Database berhasil terhubung!</div>";
}

// Set charset agar karakter khusus terbaca dengan baik
mysqli_set_charset($koneksi, "utf8");