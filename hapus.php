<?php
require_once "cek_login.php";
require_once "koneksi.php";

// Ambil ID dari URL
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: data_buku.php?status=gagal");
    exit;
}

// Cek apakah data ada
$cek = $koneksi->prepare("SELECT judul FROM books WHERE id = ?");
$cek->bind_param("i", $id);
$cek->execute();
$hasil = $cek->get_result()->fetch_assoc();
$cek->close();

if (!$hasil) {
    header("Location: data_buku.php?status=notfound");
    exit;
}

// Cegah penghapusan jika buku memiliki riwayat peminjaman
$cekPinjaman = $koneksi->prepare(
    "SELECT id FROM loans WHERE book_id = ? LIMIT 1"
);
$cekPinjaman->bind_param("i", $id);
$cekPinjaman->execute();
$memilikiPinjaman = $cekPinjaman->get_result()->fetch_assoc();
$cekPinjaman->close();

if ($memilikiPinjaman) {
    header("Location: data_buku.php?status=pinjaman_ada");
    exit;
}

// Hapus data
$stmt = $koneksi->prepare("DELETE FROM books WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: data_buku.php?status=hapus_sukses");
} else {
    header("Location: data_buku.php?status=hapus_gagal");
}
$stmt->close();
$koneksi->close();
exit;
?>
