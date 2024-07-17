<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the Dompdf library
require 'vendor/autoload.php';
include('add_signatures.php');
use Dompdf\Dompdf;
use Dompdf\Options;

// Log function
function log_message($message) {
    $log_file = 'pdf_generation.log';
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

log_message("Starting PDF generation process");

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['json_file'])) {
    $json_file = $_GET['json_file'];
    if (file_exists($json_file)) {
        $data = json_decode(file_get_contents($json_file), true);

        function format_date($date) {
            return date('d/m/y', strtotime($date));
        }

        $formattedDate = format_date($data['date']);
        log_message("Formatted date: $formattedDate");

        $htmlContent = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <title>Shift Notes PDF</title>
                <style>
                body {
                    font-family: Arial, Helvetica, sans-serif;
                    font-size: 15px;
                    background-color: lightblue;
                    padding: 10px;
                }
                .section-container {
                    background-color: white;
                    padding: 20px;
                    margin-bottom: 20px;
                    border: 3px solid black;
                }
                </style>
            </head>
            <body>
                <div class="section-container">
                    <h1>Shift Notes</h1>';

        if (!empty($data['date']) || !empty($data['worker1']) || !empty($data['timeFrom1']) || !empty($data['timeUntil1']) || !empty($data['worker2']) || !empty($data['timeFrom2']) || !empty($data['timeUntil2']) || !empty($data['client'])) {
            $htmlContent .= '<p class="section-title">Shift Details</p><br><p>*Participant does not consent to share this document</p>';
            if (!empty($data['date'])) {
                $htmlContent .= '<p><strong>Date: </strong>' . htmlspecialchars($formattedDate, ENT_QUOTES, 'UTF-8') . '</p>';
            }
            if (!empty($data['worker1'])) {
                $htmlContent .= '<p><strong>Support Worker 1: </strong>' . htmlspecialchars($data['worker1'], ENT_QUOTES, 'UTF-8') . ($data['workerStatus1'] ? ' (' . htmlspecialchars($data['workerStatus1'], ENT_QUOTES, 'UTF-8') . ')' : '') . '</p>';
            }
            if (!empty($data['timeFrom1']) || !empty($data['timeUntil1'])) {
                $htmlContent .= '<p><strong>Time From: </strong>' . htmlspecialchars($data['timeFrom1'], ENT_QUOTES, 'UTF-8') . ' - Time Until: ' . htmlspecialchars($data['timeUntil1'], ENT_QUOTES, 'UTF-8') . '</p>';
            }
            if (!empty($data['worker2'])) {
                $htmlContent .= '<p><strong>Support Worker 2:</strong> ' . htmlspecialchars($data['worker2'], ENT_QUOTES, 'UTF-8') . ($data['workerStatus2'] ? ' (' . htmlspecialchars($data['workerStatus2'], ENT_QUOTES, 'UTF-8') . ')' : '') . '</p>';
            }
            if (!empty($data['timeFrom2']) || !empty($data['timeUntil2'])) {
                $htmlContent .= '<p><strong>Time From: </strong>' . htmlspecialchars($data['timeFrom2'], ENT_QUOTES, 'UTF-8') . ' - Time Until: ' . htmlspecialchars($data['timeUntil2'], ENT_QUOTES, 'UTF-8') . '</p>';
            }
            if (!empty($data['client'])) {
                $htmlContent .= '<p><strong>Participant: </strong>' . htmlspecialchars($data['client'], ENT_QUOTES, 'UTF-8') . '</p>';
            }
        }

        if (!empty($data['extraNotes'])) {
            $htmlContent .= '
            <hr>
            <div>
                <h3>Extra Notes</h3>
                <p>' . nl2br(htmlspecialchars($data['extraNotes'], ENT_QUOTES, 'UTF-8')) . '</p>
            </div>';
        }

        if (!empty($data['appointment'])) {
            $htmlContent .= '
            <hr>
            <div>
                <h3>Appointment Details</h3>
                <p>Type: ' . htmlspecialchars($data['appointment'], ENT_QUOTES, 'UTF-8') . '</p>
            </div>';
        }

        if (!empty($data['assistanceWith1']) || !empty($data['activitiesOther1']) || !empty($data['numberOfTimes1']) || !empty($data['assistanceWith2']) || !empty($data['activitiesOther2']) || !empty($data['numberOfTimes2'])) {
            $htmlContent .= '
            <hr>
            <div class="form-group">
                <h3>Tasks</h3>';

            if (!empty($data['worker1assisted'])) {
                $htmlContent .= '<p>' . htmlspecialchars($data['worker1assisted'], ENT_QUOTES, 'UTF-8') . '</p>';
            }
            
            if (!empty($data['assistanceWith1'])) {
                $htmlContent .= '<p><strong>Assistance With: </strong>' . htmlspecialchars($data['assistanceWith1'], ENT_QUOTES, 'UTF-8') . '</p>';
            }
            if (!empty($data['activitiesOther1'])) {
                $htmlContent .= '<p><strong>Other: </strong>' . htmlspecialchars($data['activitiesOther1'], ENT_QUOTES, 'UTF-8') . '</p>';
            }
            if (!empty($data['numberOfTimes1'])) {
                $htmlContent .= '<p><strong>Number of Times: </strong>' . htmlspecialchars($data['numberOfTimes1'], ENT_QUOTES, 'UTF-8') . '</p></div>';
            }
            '<div class="form-group">';
            if (!empty($data['worker2assisted'])) {
                $htmlContent .= '<p>' . htmlspecialchars($data['worker2assisted'], ENT_QUOTES, 'UTF-8') . '</p>';
            }
            if (!empty($data['assistanceWith2'])) {
                $htmlContent .= '<p><strong>Assistance With: </strong>' . htmlspecialchars($data['assistanceWith2'], ENT_QUOTES, 'UTF-8') . '</p>';
            }
            if (!empty($data['activitiesOther2'])) {
                $htmlContent .= '<p><strong>Other: </strong>' . htmlspecialchars($data['activitiesOther2'], ENT_QUOTES, 'UTF-8') . '</p>';
            }
            if (!empty($data['numberOfTimes2'])) {
                $htmlContent .= '<p><strong>Number of Times: </strong>' . htmlspecialchars($data['numberOfTimes2'], ENT_QUOTES, 'UTF-8') . '</p></div>';
            }

            $htmlContent .= '</div>';
        }

        if (!empty($data['date']) || !empty($data['worker1']) || !empty($data['fatigueBefore1_activity1']) || !empty($data['fatigueAfter1_activity1']) || !empty($data['worker2']) || !empty($data['fatigueBefore2_activity1']) || !empty($data['fatigueAfter2_activity1'])) {
            $htmlContent .= '<div><h2>Shift Details</h2>';
            if (!empty($data['date'])) {
                $htmlContent .= '<p><strong>Date: </strong>' . htmlspecialchars($formattedDate, ENT_QUOTES, 'UTF-8') . '</p>';
            }
            if (!empty($data['worker1'])) {
                $htmlContent .= '<p><strong>Support Worker 1:</strong> ' . htmlspecialchars($data['worker1'], ENT_QUOTES, 'UTF-8') . '</p>';
            }
            if (!empty($data['activityFatigue1'])) {
                $htmlContent .= '<p><strong>Activity: </strong>' . htmlspecialchars($data['activityFatigue1'], ENT_QUOTES, 'UTF-8') . '</p>';
            }
            if (!empty($data['fatigueBefore1_activity1'])) {
                $htmlContent .= '<p><strong>Fatigue Before Activity: </strong>' . htmlspecialchars($data['fatigueBefore1_activity1'], ENT_QUOTES, 'UTF-8') . '</p>';
            }
            if (!empty($data['fatigueAfter1_activity1'])) {
                $htmlContent .= '<p><strong>Fatigue After Activity:</strong> ' . htmlspecialchars($data['fatigueAfter1_activity1'], ENT_QUOTES, 'UTF-8') . '</p>';
            }
            if (!empty($data['worker2'])) {
                $htmlContent .= '<p><strong>Support Worker 2: </strong>' . htmlspecialchars($data['worker2'], ENT_QUOTES, 'UTF-8') . '</p>';
            }
            if (!empty($data['activityFatigue2'])) {
                $htmlContent .= '<p><strong>Activity:</strong> ' . htmlspecialchars($data['activityFatigue2'], ENT_QUOTES, 'UTF-8') . '</p>';
            }
            if (!empty($data['fatigueBefore2_activity1'])) {
                $htmlContent .= '<p><strong>Fatigue Before Activity: </strong>' . htmlspecialchars($data['fatigueBefore2_activity1'], ENT_QUOTES, 'UTF-8') . '</p>';
            }
            if (!empty($data['fatigueAfter2_activity1'])) {
                $htmlContent .= '<p><strong>Fatigue After Activity: </strong>' . htmlspecialchars($data['fatigueAfter2_activity1'], ENT_QUOTES, 'UTF-8') . '</p>';
            }
        
            $htmlContent .= '</div>';
        }

        if (!empty($data['date']) || !empty($data['worker1']) || !empty($data['worker2']) || !empty($data['antecedent']) || !empty($data['behaviours']) || !empty($data['consequences']) || !empty($data['duration']) || !empty($data['environment'])) {
            $htmlContent .= '<hr>
            <div>
                <h2>ABC Data</h2>';

            if (!empty($data['worker1'])) {
                $htmlContent .= '<p><strong>Support Worker 1:</strong> ' . htmlspecialchars($data['worker1'], ENT_QUOTES, 'UTF-8') . '</p>';
            }
            if (!empty($data['worker2'])) {
                $htmlContent .= '<p><strong>Support Worker 2: </strong>' . htmlspecialchars($data['worker2'], ENT_QUOTES, 'UTF-8') . '</p>';
            }

            $htmlContent .= '<h4>Antecedents</h4>';
            if (!empty($data['antecedent'])) {
                $htmlContent .= '<p>' . nl2br(htmlspecialchars($data['antecedent'], ENT_QUOTES, 'UTF-8')) . '</p>';
            }
        
            $htmlContent .= '<h4>Behaviours</h4>';
            if (!empty($data['behaviours'])) {
                $htmlContent .= '<p>' . nl2br(htmlspecialchars($data['behaviours'], ENT_QUOTES, 'UTF-8')) . '</p>';
            }
        
            $htmlContent .= '<h4>Consequences</h4>';
            if (!empty($data['consequences'])) {
                $htmlContent .= '<p>In order to help,  ' . nl2br(htmlspecialchars($data['consequences'], ENT_QUOTES, 'UTF-8')) . '</p>';
            }
        
            $htmlContent .= '<h4>Duration</h4>';
            if (!empty($data['duration'])) {
                $htmlContent .= '<p>Incident lasted for: ' . nl2br(htmlspecialchars($data['duration'], ENT_QUOTES, 'UTF-8')) . '</p>';
            }

            $htmlContent .= '<h4>Environment</h4>';
            if (!empty($data['environment'])) {
                $htmlContent .= '<p>Incident took place: ' . nl2br(htmlspecialchars($data['environment'], ENT_QUOTES, 'UTF-8')) . '</p>';
            }

            $htmlContent .= '</div>';
        }

        if (!empty($data['signature1']) || !empty($data['signature2'])) {
            $htmlContent .= '<div style="border: 1px solid black;">';
            if (!empty($data['signature1'])) {
                $htmlContent .= '<p>Support Worker 1 Signature</p><br><div class="signature-box"><img src="' . htmlspecialchars($data['signature1'], ENT_QUOTES, 'UTF-8') . '" alt="Signature 1" /></div>';
            }
            if (!empty($data['signature2'])) {
                $htmlContent .= '<p>Support Worker 2 Signature</p><br><div class="signature-box"><img src="' . htmlspecialchars($data['signature2'], ENT_QUOTES, 'UTF-8') . '" alt="Signature 2" /></div>';
            }
            $htmlContent .= '</div>';
        }

        $htmlContent .= '</body></html>';

        try {
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $dompdf = new Dompdf($options);

            $dompdf->loadHtml($htmlContent);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $outputFile = 'shift_details_' . time() . '.pdf';
            file_put_contents($outputFile, $dompdf->output());

            log_message("PDF generated successfully and saved to $outputFile");

            echo json_encode([
                'status' => 'success',
                'message' => 'PDF generated successfully',
                'pdf_path' => $outputFile
            ]);
        } catch (Exception $e) {
            log_message("Error generating PDF: " . $e->getMessage());
            echo json_encode([
                'status' => 'error',
                'message' => 'Error generating PDF: ' . $e->getMessage()
            ]);
        }
    } else {
        log_message("JSON file not found");
        echo json_encode([
            'status' => 'error',
            'message' => 'JSON file not found'
        ]);
    }
} else {
    log_message("Invalid request method");
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
}
?>
