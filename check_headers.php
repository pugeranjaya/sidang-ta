<?php
require __DIR__.'/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
$spreadsheet = IOFactory::load('C:\xampp\htdocs\sidang-ta\PENDAFTAR TA2_JUNI-26.xlsx');
$rows = $spreadsheet->getActiveSheet()->toArray();
print_r($rows[0]);
print_r($rows[1]);
