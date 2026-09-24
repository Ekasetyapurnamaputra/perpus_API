<?php
// =====================================================
// API JSON - Data Buku
// URL: http://localhost/perpus_api/data_json.php
// =====================================================
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

require_once "koneksi.php";

// Ambil semua data buku
$result = $koneksi->query("SELECT id, kode_buku, judul, penulis, kategori, tahun_terbit, penerbit 
                           FROM books 
                           ORDER BY id ASC");

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        "id"           => $row['id'],
        "kode_buku"    => $row['kode_buku'],
        "judul"        => $row['judul'],
        "penulis"      => $row['penulis'],
        "kategori"     => $row['kategori'],
        "tahun_terbit" => $row['tahun_terbit'],
        "penerbit"     => $row['penerbit']
    ];
}

// Kirim respon JSON
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

$koneksi->close();
?>