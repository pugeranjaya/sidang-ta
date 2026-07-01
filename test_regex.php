<?php
$nama = 'Prof. Ir. Nur Indrianti, M.T., D.Eng., IPU, ASEAN Eng.';
$clean = preg_replace('/(prof|dr|ir|s\.t|m\.t|d\.eng|ipu|asean eng|m\.sc|m\.eng|s\.tp|s\.si|m\.si|ph\.d|m\.kom|m\.hum|dra)\.?/i', '', $nama);
$cleanNameForSearch = '%' . preg_replace('/[^a-z]/', '%', strtolower($clean)) . '%';
echo json_encode(['original' => $nama, 'clean' => $clean, 'search' => $cleanNameForSearch], JSON_PRETTY_PRINT);
