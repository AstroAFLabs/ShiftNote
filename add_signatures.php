<?php
require_once('vendor/autoload.php');

use setasign\Fpdi\Fpdi;

function add_signatures_to_pdf($input_pdf, $output_pdf, $signature_paths) {
    // Initialize FPDI
    $pdf = new FPDI();

    // Add a page from the existing PDF
    $pageCount = $pdf->setSourceFile($input_pdf);
    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
        $templateId = $pdf->importPage($pageNo);
        $size = $pdf->getTemplateSize($templateId);

        // Create a new page with the same size
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($templateId);

        // Overlay signatures
        $y_position = 150;
        foreach ($signature_paths as $signature) {
            $pdf->Image($signature, 10, $y_position, 50, 30); // Adjust position and size as needed
            $y_position += 50;
        }
    }

    // Output the new PDF
    $pdf->Output($output_pdf, 'F');
}

// Ensure the function only runs when included and necessary variables are set
if (isset($input_pdf) && isset($output_pdf) && isset($signature_paths)) {
    add_signatures_to_pdf($input_pdf, $output_pdf, $signature_paths);
}
?>
