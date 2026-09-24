<?php
require_once "cek_login.php";
require_once "koneksi.php";
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Judul
$sheet->mergeCells('A1:G1');
$sheet->setCellValue('A1', 'LAPORAN DATA BUKU PERPUSTAKAAN');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Header
$headers = ['NO', 'KODE BUKU', 'JUDUL', 'PENULIS', 'KATEGORI', 'TAHUN TERBIT', 'PENERBIT'];
$kolom = 'A';
foreach ($headers as $h) {
    $sheet->setCellValue($kolom . '3', $h);
    $kolom++;
}

// Styling header
$sheet->getStyle('A3:G3')->applyFromArray([
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C3E50']],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical'   => Alignment::VERTICAL_CENTER
    ],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
]);

// Data
$result = $koneksi->query("SELECT * FROM books ORDER BY id ASC");
$row = 4; $no = 1;
while ($b = $result->fetch_assoc()) {
    $sheet->setCellValue('A' . $row, $no++);
    $sheet->setCellValue('B' . $row, $b['kode_buku']);
    $sheet->setCellValue('C' . $row, $b['judul']);
    $sheet->setCellValue('D' . $row, $b['penulis']);
    $sheet->setCellValue('E' . $row, $b['kategori']);
    $sheet->setCellValue('F' . $row, $b['tahun_terbit']);
    $sheet->setCellValue('G' . $row, $b['penerbit']);
    $row++;
}

// Border untuk data
$sheet->getStyle('A4:G' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

// Auto size kolom
foreach (range('A', 'G') as $c) {
    $sheet->getColumnDimension($c)->setAutoSize(true);
}

// Nama file
$filename = "data_buku_" . date("Ymd_His") . ".xlsx";

header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Cache-Control: max-age=0");

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;