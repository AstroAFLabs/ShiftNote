<?php
// download_pdf.php

if (!isset($_GET['file']) || empty($_GET['file'])) {
    die('File not specified.');
}

$file = urldecode($_GET['file']);

if (file_exists($file)) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;
} else {
    die('File does not exist.');
}
?>
