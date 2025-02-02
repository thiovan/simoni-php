<?php
// Jika file ini diakses langsung, maka redirek ke halaman index
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    header("Location: index.php");
    exit;
}

// Konfigurasi database
define('DB_HOST', 'localhost'); // Ganti dengan host database Anda
define('DB_NAME', 'simoni_db'); // Ganti dengan nama database Anda
define('DB_USER', 'root'); // Ganti dengan username database Anda
define('DB_PASS', ''); // Ganti dengan password database Anda

// Pengaturan aplikasi
define('APP_NAME', 'Si Moni'); // Ganti dengan nama aplikasi Anda
define('APP_AUTHOR', 'NAMA_ANDA'); // Ganti dengan pembuat aplikasi Anda
