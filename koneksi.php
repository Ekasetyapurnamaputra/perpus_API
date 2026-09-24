<?php
// =====================================================
// KONEKSI DATABASE
// Aplikasi Data Buku Perpustakaan
// =====================================================

$host     = "localhost";
$user     = "root";
$password = "";           // default XAMPP kosong
$database = "perpus_api";

// Buat koneksi menggunakan MySQLi
$koneksi = new mysqli($host, $user, $password, $database);

// Cek koneksi
if ($koneksi->connect_error) {
    die("Koneksi database gagal: " . $koneksi->connect_error);
}

// Set charset agar mendukung karakter UTF-8
$koneksi->set_charset("utf8mb4");

// Set timezone (opsional)
date_default_timezone_set("Asia/Jakarta");
?>