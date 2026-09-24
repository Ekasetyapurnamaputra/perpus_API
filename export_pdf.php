<?php
require_once "cek_login.php";
require_once "koneksi.php";
require 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// Ambil data
$result = $koneksi->query("SELECT * FROM books ORDER BY id ASC");
$books = $result->fetch_all(MYSQLI_ASSOC);

// Build HTML
$html = '
<style>
    body { font-family: sans-serif; font-size: 11px; }
    h2 { text-align: center; margin: 0; }
    h4 { text-align: center; margin: 5px 0; font-weight: normal; color: #555; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th { background: #2c3e50; color: #fff; padding: 8px; border: 1px solid #000; }
    td { padding: 6px 8px; border: 1px solid #444; }
    tr:nth-child(even) { background: #f4f6f9; }
    .footer { margin-top: 20px; font-style: italic; font-size: 10px; text-align: right; }
</style>
<h2>LAPORAN DATA BUKU PERPUSTAKAAN</h2>
<h4>SMK PGRI 2 PONOROGO - XII RPL 1</h4>
<h4>Dicetak: ' . date('d-m-Y H:i:s') . '</h4>

<table>
    <thead>
        <tr>
            <th>NO</th>
            <th>KODE</th>
            <th>JUDUL</th>
            <th>PENULIS</th>
            <th>KATEGORI</th>
            <th>TAHUN</th>
            <th>PENERBIT</th>
        </tr>
    </thead>
    <tbody>';

$no = 1;
foreach ($books as $b) {
    $html .= '
        <tr>
            <td style="text-align:center;">' . $no++ . '</td>
            <td>' . htmlspecialchars($b['kode_buku']) . '</td>
            <td>' . htmlspecialchars($b['judul']) . '</td>
            <td>' . htmlspecialchars($b['penulis']) . '</td>
            <td>' . htmlspecialchars($b['kategori']) . '</td>
            <td style="text-align:center;">' . $b['tahun_terbit'] . '</td>
            <td>' . htmlspecialchars($b['penerbit']) . '</td>
        </tr>';
}

$html .= '
    </tbody>
</table>
<div class="footer">Total: ' . count($books) . ' buku</div>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream('laporan_data_buku.pdf', ['Attachment' => false]);
exit;