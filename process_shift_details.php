<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the TCPDF library (update this path based on your actual directory structure)
require_once('vendor/tecnickcom/tcpdf/tcpdf.php');

// Log function
function log_message($message) {
    $log_file = 'pdf_generation.log';
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

// Function to load participant data
function loadParticipantData($participant) {
    $filePath = __DIR__ . "/participants/{$participant}.php";
    if (file_exists($filePath)) {
        return include($filePath);
    }
    return []; // Return an empty array if the file does not exist
}

// Define the worker images array
$worker_images = array(
    "Andrea Fajardo" => array("jpg" => "images_converted/Andrea_Fajardo.jpg", "png" => "images/Andrea_Fajardo.png"),
    "Angela Lee" => array("jpg" => "images_converted/Angela_Lee.jpg", "png" => "images/Angela_Lee.png"),
    "Angela Zefi" => array("jpg" => "images_converted/Angela_Zefi.jpg", "png" => "images/Angela_Zefi.png"),
    "Anika Bartholomeusz" => array("jpg" => "images_converted/Anika_Bartholomeusz.jpg", "png" => "images/Anika_Bartholomeusz.png"),
    "Benjamin (Ben) Roberts" => array("jpg" => "images_converted/Benjamin_(Ben)_Roberts.jpg", "png" => "images/Benjamin_(Ben)_Roberts.png"),
    "Bronte (Bunny) Pettman" => array("jpg" => "images_converted/Bronte_(Bunny)_Pettman.jpg", "png" => "images/Bronte_(Bunny)_Pettman.png"),
    "Chaynne Humphrys" => array("jpg" => "images_converted/Chaynne_Humphrys.jpg", "png" => "images/Chaynne_Humphrys.png"),
    "Chi Ha" => array("jpg" => "images_converted/Chi_Ha.jpg", "png" => "images/Chi_Ha.png"),
    "Courtney Millington" => array("jpg" => "images_converted/Courtney_Millington.jpg", "png" => "images/Courtney_Millington.png"),
    "Dhara Patel" => array("jpg" => "images_converted/Dhara_Patel.jpg", "png" => "images/Dhara_Patel.png"),
    "Dhruv Sabharwal" => array("jpg" => "images_converted/Dhruv_Sabharwal.jpg", "png" => "images/Dhruv_Sabharwal.png"),
    "Fernando Arnibar" => array("jpg" => "images_converted/Fernando_Arnibar.jpg", "png" => "images/Fernando_Arnibar.png"),
    "Gaumit Patel" => array("jpg" => "images_converted/Gaumit_Patel.jpg", "png" => "images/Gaumit_Patel.png"),
    "Harshkumar (Happy) Modi" => array("jpg" => "images_converted/Harshkumar_(Happy)_Modi.jpg", "png" => "images/Harshkumar_(Happy)_Modi.png"),
    "Japneet (Rosie) Kaur" => array("jpg" => "images_converted/Japneet_(Rosie)_Kaur.jpg", "png" => "images/Japneet_(Rosie)_Kaur.png"),
    "Jarryd Humphrys" => array("jpg" => "images_converted/Jarryd_Humphrys.jpg", "png" => "images/Jarryd_Humphrys.png"),
    "Joe Zhang" => array("jpg" => "images_converted/Joe_Zhang.jpg", "png" => "images/Joe_Zhang.png"),
    "Jose Diaz Armijo" => array("jpg" => "images_converted/Jose_Diaz_Armijo.jpg", "png" => "images/Jose_Diaz_Armijo.png"),
    "Joseph Espejo" => array("jpg" => "images_converted/Joseph_Espejo.jpg", "png" => "images/Joseph_Espejo.png"),
    "Julie Moore" => array("jpg" => "images_converted/Julie_Moore.jpg", "png" => "images/Julie_Moore.png"),
    "Kapil Lamichane" => array("jpg" => "images_converted/Kapil_Lamichane.jpg", "png" => "images/Kapil_Lamichane.png"),
    "Margerite (Rita) Gjergji" => array("jpg" => "images_converted/Margerite_(Rita)_Gjergji.jpg", "png" => "images/Margerite_(Rita)_Gjergji.png"),
    "Marie (Maz) Andersen" => array("jpg" => "images_converted/Marie_(Maz)_Andersen.jpg", "png" => "images/Marie_(Maz)_Andersen.png"),
    "Meena Sapkota" => array("jpg" => "images_converted/Meena_Sapkota.jpg", "png" => "images/Meena_Sapkota.png"),
    "Muhammad Hamza" => array("jpg" => "images_converted/Muhammad_Hamza.jpg", "png" => "images/Muhammad_Hamza.png"),
    "Otto Basson" => array("jpg" => "images_converted/Otto_Basson.jpg", "png" => "images/Otto_Basson.png"),
    "Parvinder Kaur" => array("jpg" => "images_converted/Parvinder_Kaur.jpg", "png" => "images/Parvinder_Kaur.png"),
    "Pranvera Ymeraj" => array("jpg" => "images_converted/Pranvera_Ymeraj.jpg", "png" => "images/Pranvera_Ymeraj.png"),
    "Robandeep (Roban) Singh" => array("jpg" => "images_converted/Robandeep_(Roban)_Singh.jpg", "png" => "images/Robandeep_(Roban)_Singh.png"),
    "Rodrigo Tangol" => array("jpg" => "images_converted/Rodrigo_Tangol.jpg", "png" => "images/Rodrigo_Tangol.png"),
    "Sandy Tran" => array("jpg" => "images_converted/Sandy_Tran.jpg", "png" => "images/Sandy_Tran.png"),
    "Victoria Horsfall" => array("jpg" => "images_converted/Victoria_Horsfall.jpg", "png" => "images/Victoria_Horsfall.png"),
    "Zachary (Zac) Gawen" => array("jpg" => "images_converted/Zachary_(Zac)_Gawen.jpg", "png" => "images/Zachary_(Zac)_Gawen.png"),
);

// Signature positions
$signature_positions = [
    "signatureBox1" => [50, 200, 50, 30],  // x, y, width, height
    "signatureBox2" => [150, 200, 50, 30],
];

// Database connection
$pdo = new PDO('mysql:host=localhost;dbname=narulaswdb', 'root');

// Function to save form data
function saveFormData($formData) {
    global $pdo;

    // Debugging: Log the form data
    log_message("Form data: " . print_r($formData, true));

    $stmt = $pdo->prepare("
        INSERT INTO shift_reports (
            date, worker_status, worker1, timeFrom1, timeUntil1, 
            worker_status2, worker2, timeFrom2, timeUntil2, client,
            extra_notes1, extra_notes2, appointment, worker1assisted, worker2assisted, 
            assistanceWith1, activities_other1, numberoftimes1, assistanceWith2, activities_other2, 
            numberoftimes2, fatigueBefore1_activity1, fatigueAfter1_activity1, activityFatigue1, 
            activityFatigue2, fatigueBefore2_activity1, fatigueAfter2_activity1, worker6, 
            antecedent, behaviour, consequence, incident_duration, it_happened_where, 
            updated_by
        ) VALUES (
            :date, :worker_status, :worker1, :timeFrom1, :timeUntil1, 
            :worker_status2, :worker2, :timeFrom2, :timeUntil2, :client,
            :extra_notes1, :extra_notes2, :appointment, :worker1assisted, :worker2assisted, 
            :assistanceWith1, :activities_other1, :numberoftimes1, :assistanceWith2, :activities_other2, 
            :numberoftimes2, :fatigueBefore1_activity1, :fatigueAfter1_activity1, :activityFatigue1, 
            :activityFatigue2, :fatigueBefore2_activity1, :fatigueAfter2_activity1, :worker6, 
            :antecedent, :behaviour, :consequence, :incident_duration, :it_happened_where, 
            :updated_by
        )
    ");
    $stmt->execute([
        ':date' => $formData['date'] ?? null,
        ':worker_status' => $formData['worker_status'] ?? null,
        ':worker1' => $formData['worker1'] ?? null,
        ':timeFrom1' => $formData['timeFrom1'] ?? null,
        ':timeUntil1' => $formData['timeUntil1'] ?? null,
        ':worker_status2' => $formData['worker_status2'] ?? null,
        ':worker2' => $formData['worker2'] ?? null,
        ':timeFrom2' => $formData['timeFrom2'] ?? null,
        ':timeUntil2' => $formData['timeUntil2'] ?? null,
        ':client' => $formData['client'] ?? null,
        ':extra_notes1' => $formData['extra_notes1'] ?? null,
        ':extra_notes2' => $formData['extra_notes2'] ?? null,
        ':appointment' => $formData['appointment'] ?? null,
        ':worker1assisted' => $formData['worker1assisted'] ?? null,
        ':worker2assisted' => $formData['worker2assisted'] ?? null,
        ':assistanceWith1' => $formData['assistanceWith1'] ?? null,
        ':activities_other1' => $formData['activities_other1'] ?? null,
        ':numberoftimes1' => $formData['numberoftimes1'] ?? null,
        ':assistanceWith2' => $formData['assistanceWith2'] ?? null,
        ':activities_other2' => $formData['activities_other2'] ?? null,
        ':numberoftimes2' => $formData['numberoftimes2'] ?? null,
        ':fatigueBefore1_activity1' => $formData['fatigueBefore1_activity1'] ?? null,
        ':fatigueAfter1_activity1' => $formData['fatigueAfter1_activity1'] ?? null,
        ':activityFatigue1' => $formData['activityFatigue1'] ?? null,
        ':activityFatigue2' => $formData['activityFatigue2'] ?? null,
        ':fatigueBefore2_activity1' => $formData['fatigueBefore2_activity1'] ?? null,
        ':fatigueAfter2_activity1' => $formData['fatigueAfter2_activity1'] ?? null,
        ':worker6' => $formData['worker6'] ?? null,
        ':antecedent' => $formData['antecedent'] ?? null,
        ':behaviour' => $formData['behaviour'] ?? null,
        ':consequence' => $formData['consequence'] ?? null,
        ':incident_duration' => $formData['incident_duration'] ?? null,
        ':it_happened_where' => $formData['it_happened_where'] ?? null,
        ':updated_by' => $formData['worker1'] ?? null
    ]);
    return $pdo->lastInsertId();
}

// Function to retrieve form data
function getFormData($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT data FROM shift_reports WHERE id = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['data'];
}

// Function to delete form data
function deleteFormData($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM shift_reports WHERE id = :id");
    $stmt->execute([':id' => $id]);
}

function updateFormData($id, $formData) {
    global $pdo;
    $stmt = $pdo->prepare("
        UPDATE shift_reports SET 
            date = :date, worker_status = :worker_status, worker1 = :worker1, timeFrom1 = :timeFrom1, 
            timeUntil1 = :timeUntil1, worker_status2 = :worker_status2, worker2 = :worker2, timeFrom2 = :timeFrom2, 
            timeUntil2 = :timeUntil2, client = :client, extra_notes1 = :extra_notes1, extra_notes2 = :extra_notes2, 
            appointment = :appointment, worker1assisted = :worker1assisted, worker2assisted = :worker2assisted, 
            assistanceWith1 = :assistanceWith1, activities_other1 = :activities_other1, numberoftimes1 = :numberoftimes1, 
            assistanceWith2 = :assistanceWith2, activities_other2 = :activities_other2, numberoftimes2 = :numberoftimes2, 
            fatigueBefore1_activity1 = :fatigueBefore1_activity1, fatigueAfter1_activity1 = :fatigueAfter1_activity1, 
            activityFatigue1 = :activityFatigue1, activityFatigue2 = :activityFatigue2, fatigueBefore2_activity1 = :fatigueBefore2_activity1, 
            fatigueAfter2_activity1 = :fatigueAfter2_activity1, worker6 = :worker6, antecedent = :antecedent, behaviour = :behaviour, 
            consequence = :consequence, incident_duration = :incident_duration, it_happened_where = :it_happened_where, updated_by = :updated_by 
        WHERE id = :id
    ");
    $stmt->execute([
        ':date' => $formData['date'] ?? null,
        ':worker_status' => $formData['worker_status'] ?? null,
        ':worker1' => $formData['worker1'] ?? null,
        ':timeFrom1' => $formData['timeFrom1'] ?? null,
        ':timeUntil1' => $formData['timeUntil1'] ?? null,
        ':worker_status2' => $formData['worker_status2'] ?? null,
        ':worker2' => $formData['worker2'] ?? null,
        ':timeFrom2' => $formData['timeFrom2'] ?? null,
        ':timeUntil2' => $formData['timeUntil2'] ?? null,
        ':client' => $formData['client'] ?? null,
        ':extra_notes1' => $formData['extra_notes1'] ?? null,
        ':extra_notes2' => $formData['extra_notes2'] ?? null,
        ':appointment' => $formData['appointment'] ?? null,
        ':worker1assisted' => $formData['worker1assisted'] ?? null,
        ':worker2assisted' => $formData['worker2assisted'] ?? null,
        ':assistanceWith1' => $formData['assistanceWith1'] ?? null,
        ':activities_other1' => $formData['activities_other1'] ?? null,
        ':numberoftimes1' => $formData['numberoftimes1'] ?? null,
        ':assistanceWith2' => $formData['assistanceWith2'] ?? null,
        ':activities_other2' => $formData['activities_other2'] ?? null,
        ':numberoftimes2' => $formData['numberoftimes2'] ?? null,
        ':fatigueBefore1_activity1' => $formData['fatigueBefore1_activity1'] ?? null,
        ':fatigueAfter1_activity1' => $formData['fatigueAfter1_activity1'] ?? null,
        ':activityFatigue1' => $formData['activityFatigue1'] ?? null,
        ':activityFatigue2' => $formData['activityFatigue2'] ?? null,
        ':fatigueBefore2_activity1' => $formData['fatigueBefore2_activity1'] ?? null,
        ':fatigueAfter2_activity1' => $formData['fatigueAfter2_activity1'] ?? null,
        ':worker6' => $formData['worker6'] ?? null,
        ':antecedent' => $formData['antecedent'] ?? null,
        ':behaviour' => $formData['behaviour'] ?? null,
        ':consequence' => $formData['consequence'] ?? null,
        ':incident_duration' => $formData['incident_duration'] ?? null,
        ':it_happened_where' => $formData['it_happened_where'] ?? null,
        ':updated_by' => $formData['worker1'] ?? null,
        ':id' => $id
    ]);
}

// Function to sync files
function syncFiles($id, $formData) {
    global $pdo;
    $existingData = getFormData($id);
    if ($existingData !== json_encode($formData, JSON_PRETTY_PRINT)) {
        updateFormData($id, $formData);
        // Update the PDF file if needed
        $pdfFilename = 'shift_report_' . $id . '.pdf';
        $htmlContent = "<html>...your HTML content here...</html>"; // Use actual HTML content from form
        generatePDF($htmlContent, $pdfFilename);
    }
}

// Helper function to get POST data with a default value
function get_post($key, $default = '') {
    return isset($_POST[$key]) ? $_POST[$key] : $default;
}

// Function to format the date as dd/mm/yy
function format_date($date) {
    return date('d/m/y', strtotime($date));
}

// Function to generate PDF from HTML
function generatePDF($htmlContent, $filename) {
    $pdf = new TCPDF();
    $pdf->AddPage();
    $pdf->writeHTML($htmlContent);
    $pdf->Output($filename, 'F');
}

// Function to generate JSON from form data
function generateJSON($formData, $filename) {
    file_put_contents($filename, json_encode($formData, JSON_PRETTY_PRINT));
}

log_message("Starting PDF generation process");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Retrieve form data using the helper function
        $formData = $_POST;
        $date = get_post('date');
        $workerStatus1 = get_post('worker-status');
        $worker1 = get_post('worker1');
        $timeFrom1 = get_post('timeFrom1');
        $timeUntil1 = get_post('timeUntil1');
        $workerStatus2 = get_post('worker-status2');
        $worker2 = get_post('worker2');
        $timeFrom2 = get_post('timeFrom2');
        $timeUntil2 = get_post('timeUntil2');
        $client = get_post('client');
        $extraNotes = get_post('extra-notes1');
        $extraNotes2 = get_post('extra-notes2');
        $appointment = get_post('appointment');
        $worker1assisted = get_post('worker1assisted');
        $worker2assisted = get_post('worker2assisted');
        $assistanceWith1 = get_post('assistanceWith1');
        $activitiesOther1 = get_post('activities-other1');
        $numberOfTimes1 = get_post('numberoftimes1');
        $assistanceWith2 = get_post('assistanceWith2');
        $activitiesOther2 = get_post('activities-other2');
        $numberOfTimes2 = get_post('numberoftimes2');
        $fatigueBefore1_activity1 = get_post('fatigueBefore1-activity1');
        $fatigueAfter1_activity1 = get_post('fatigueAfter1-activity1');
        $activityFatigue1 = get_post('activityFatigue1');
        $activityFatigue2 = get_post('activityFatigue2');
        $fatigueBefore2_activity1 = get_post('fatigueBefore2_activity1');
        $fatigueAfter2_activity1 = get_post('fatigueAfter2_activity1');
        $worker6 = get_post('worker6');
        $antecedent = get_post('antecedent');
        $behaviours = get_post('behaviour');
        $consequences = get_post('consequence');
        $duration = get_post('incident-duration');
        $environment = get_post('it-happened-where');

        // Ensure all variables are defined
        $workerStatus1 = $workerStatus1 ?? '';
        $workerStatus2 = $workerStatus2 ?? '';

        // Adjust worker names to match keys in the worker_images array
        $worker1_key = str_replace(' ', '_', $worker1);
        $worker2_key = str_replace(' ', '_', $worker2);

        // Debugging: Log adjusted worker keys
        log_message("Adjusted worker1 key: " . $worker1_key);
        log_message("Adjusted worker2 key: " . $worker2_key);

        // Function to get the appropriate image path (jpg or png)
        function get_image_path($worker_name, $format, $worker_images) {
            if (isset($worker_images[$worker_name]) && isset($worker_images[$worker_name][$format])) {
                return $worker_images[$worker_name][$format];
            }
            return null;
        }

        // Get the image paths for the workers
        $image_path1 = get_image_path($worker1, 'jpg', $worker_images); // You can change 'jpg' to 'png' as needed
        $image_path2 = get_image_path($worker2, 'jpg', $worker_images); // You can change 'jpg' to 'png' as needed

        // Initialize TCPDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Set document information
        $pdf->SetAuthor('NarulaFam Dream Team');
        $pdf->SetTitle('Shift Notes');
        $pdf->SetSubject('Shift Notes Report');
        $pdf->SetKeywords('TCPDF, PDF, example, test, guide');

        // Set default header and footer data
        $pdf->setHeaderData(false);
        $pdf->setFooterData(false);

        // Set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // Set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // Set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // Add a page
        $pdf->AddPage();

        // Set font
        $pdf->SetFont('helvetica', '', 12);

        // Format the date
        $formattedDate = format_date($date);

        // Create the report PDF content
        $htmlContent = '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Shift Notes PDF</title>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
            <style>
                body {
                    font-family: Arial, Helvetica, sans-serif;
                    font-size: 12px;
                    padding: 10px;
                }
                .section-title {
                    font-size: 16px;
                    font-weight: bold;
                    margin-top: 20px;
                }
                section {
                    border: 1px solid black;
                    padding: 10px;
                    margin: 10px;
                }
                .signature {
                    display: inline;
                    width: 250px;
                    height: 150px;
                    border: 1px solid black;
                }
            </style>
        </head>
        <body>
            <h1>Shift Notes</h1>';

        if (!empty($date) || !empty($worker1) || !empty($timeFrom1) || !empty($timeUntil1) || !empty($worker2) || !empty($timeFrom2) || !empty($timeUntil2) || !empty($client)) {
            $htmlContent .= '<p class="section-title">Shift Details</p>';
            if (!empty($date)) {
                $htmlContent .= '<p><strong>Date: </strong>' . htmlspecialchars($formattedDate, ENT_QUOTES, 'UTF-8') . '</p>';
            }
            if (!empty($worker1)) {
                $htmlContent .= '<p><strong>Support Worker 1: </strong>' . htmlspecialchars($worker1, ENT_QUOTES, 'UTF-8')  . ' --- ' . htmlspecialchars($workerStatus1, ENT_QUOTES, 'UTF-8')  . '</p>';
            }
            if (!empty($timeFrom1) || !empty($timeUntil1)) {
                $htmlContent .= '<p><strong>Shift Duration: </strong>' . htmlspecialchars($timeFrom1, ENT_QUOTES, 'UTF-8') . ' --- ' . htmlspecialchars($timeUntil1, ENT_QUOTES, 'UTF-8') . '</p>';
            }
            if (!empty($worker2)) {
                $htmlContent .= '<p><strong>Support Worker 2:</strong> ' . htmlspecialchars($worker2, ENT_QUOTES, 'UTF-8') . ' --- ' . htmlspecialchars($workerStatus2, ENT_QUOTES, 'UTF-8')  . '</p>';
            }
            if (!empty($timeFrom2) || !empty($timeUntil2)) {
                $htmlContent .= '<p><strong>Shift Duration: </strong>' . htmlspecialchars($timeFrom2, ENT_QUOTES, 'UTF-8') . ' --- ' . htmlspecialchars($timeUntil2, ENT_QUOTES, 'UTF-8') . '</p>';
            }
            if (!empty($client)) {
                $htmlContent .= '<p><strong>Participant: </strong>' . htmlspecialchars($client, ENT_QUOTES, 'UTF-8') . '</p>';
            }
        }

        if (!empty($extraNotes)) {
            $htmlContent .= '<hr><div><h3>Notes</h3><p>' . nl2br(htmlspecialchars($extraNotes, ENT_QUOTES, 'UTF-8')) . '</p></div>';
        }
        if (!empty($extraNotes2)) {
            $htmlContent .= '<hr><div><h3>Extra Notes</h3><p>' . nl2br(htmlspecialchars($extraNotes2, ENT_QUOTES, 'UTF-8')) . '</p></div>';
        }
        if (!empty($appointment)) {
            $htmlContent .= '<hr><div><h3>Appointment Details</h3><p>Type: ' . htmlspecialchars($appointment, ENT_QUOTES, 'UTF-8') . '</p></div>';
        }
        if (!empty($assistanceWith1) || !empty($activitiesOther1) || !empty($numberOfTimes1) || !empty($assistanceWith2) || !empty($activitiesOther2) || !empty($numberOfTimes2)) {
            $htmlContent .= '<hr><div class="form-group"><h3>Tasks</h3>';
            if (!empty($worker1assisted)) {
                $htmlContent .= '<p>' . htmlspecialchars($worker1assisted, ENT_QUOTES, 'UTF-8') . '</p>';
            }
            if (!empty($assistanceWith1)) {
                $htmlContent .= '<p><strong>Assistance With: </strong>' . htmlspecialchars($assistanceWith1, ENT_QUOTES, 'UTF-8') . '</p>';
            }
            if (!empty($activitiesOther1)) {
                $htmlContent .= '<p><strong>Other: </strong>' . htmlspecialchars($activitiesOther1, ENT_QUOTES, 'UTF-8') . '</p>';
            }
            if (!empty($numberOfTimes1)) {
                $htmlContent .= '<p><strong>Number of Times: </strong>' . htmlspecialchars($numberOfTimes1, ENT_QUOTES, 'UTF-8') . '</p>';
            }
            if (!empty($worker2assisted)) {
                $htmlContent .= '<p>' . htmlspecialchars($worker2assisted, ENT_QUOTES, 'UTF-8') . '</p>';
            }
            if (!empty($assistanceWith2)) {
                $htmlContent .= '<p><strong>Assistance With: </strong>' . htmlspecialchars($assistanceWith2, ENT_QUOTES, 'UTF-8') . '</p>';
            }
            if (!empty($activitiesOther2)) {
                $htmlContent .= '<p><strong>Other: </strong>' . htmlspecialchars($activitiesOther2, ENT_QUOTES, 'UTF-8') . '</p>';
            }
            if (!empty($numberOfTimes2)) {
                $htmlContent .= '<p><strong>Number of Times: </strong>' . htmlspecialchars($numberOfTimes2, ENT_QUOTES, 'UTF-8') . '</p></div>';
            }
            $htmlContent .= '</div>';
        }

        if (!empty($date) || !empty($worker1) || !empty($antecedent) || !empty($behaviours) || !empty($duration) || !empty($environment)) {
            $htmlContent .= '<hr><div><h2>ABC Data</h2>';
            if (!empty($worker1)) {
                $htmlContent .= '<p><strong>Support Worker 1:</strong> ' . htmlspecialchars($worker1, ENT_QUOTES, 'UTF-8') . '</p>';
            }
            if (!empty($worker2)) {
                $htmlContent .= '<p><strong>Support Worker 2: </strong>' . htmlspecialchars($worker2, ENT_QUOTES, 'UTF-8') . '</p>';
            }

            // Antecedents
            $htmlContent .= '<h4>Antecedents</h4>';
            if (!empty($antecedent)) {
                $htmlContent .= '<p>' . nl2br(htmlspecialchars($antecedent, ENT_QUOTES, 'UTF-8')) . '</p>';
            }

            // Behaviours
            $htmlContent .= '<h4>Behaviours</h4>';
            if (!empty($behaviours)) {
                $htmlContent .= '<p>' . nl2br(htmlspecialchars($behaviours, ENT_QUOTES, 'UTF-8')) . '</p>';
            }

            // Consequences
            $htmlContent .= '<h4>Consequences</h4>';
            if (!empty($consequences)) {
                $htmlContent .= '<p>' . nl2br(htmlspecialchars($consequences, ENT_QUOTES, 'UTF-8')) . '</p>';
            }

            // Duration
            $htmlContent .= '<h4>Duration</h4>';
            if (!empty($duration)) {
                $htmlContent .= '<p>' . nl2br(htmlspecialchars($duration, ENT_QUOTES, 'UTF-8')) . '</p>';
            }

            // Environment
            $htmlContent .= '<h4>Environment</h4>';
            if (!empty($environment)) {
                $htmlContent .= '<p>' . nl2br(htmlspecialchars($environment, ENT_QUOTES, 'UTF-8')) . '</p>';
            }

            $htmlContent .= '</div>';
        }

        $htmlContent .= '<hr><div class="signatures row">';
        if (!empty($image_path1) && file_exists($image_path1)) {
            $htmlContent .= '<p>Support Worker 1 Signature</p><img class="signature" src="' . htmlspecialchars($image_path1, ENT_QUOTES, 'UTF-8') . '" alt="Signature 1" />';
        }
        if (!empty($image_path2) && file_exists($image_path2)) {
            $htmlContent .= '<p>Support Worker 2 Signature</p><img class="signature" src="' . htmlspecialchars($image_path2, ENT_QUOTES, 'UTF-8') . '" alt="Signature 2" />';
        }
        $htmlContent .= '</div></div>';

        $htmlContent .= '</body></html>';

        // Print text using writeHTMLCell()
        $pdf->writeHTMLCell(0, 0, '', '', $htmlContent, 0, 1, 0, true, '', true);

        // Place the signatures in the PDF
        if ($image_path1 && isset($signature_positions["signatureBox1"])) {
            list($x, $y, $w, $h) = $signature_positions["signatureBox1"];
            $pdf->Image($image_path1, $x, $y, $w, $h, 'JPG', '', 'T', false, 300, '', false, false, 1, false, false, false);
        }

        if ($image_path2 && isset($signature_positions["signatureBox2"])) {
            list($x, $y, $w, $h) = $signature_positions["signatureBox2"];
            $pdf->Image($image_path2, $x, $y, $w, $h, 'JPG', '', 'T', false, 300, '', false, false, 1, false, false, false);
        }

        // Generate a unique file name using the worker's name and the formatted date
        $fileName = 'shift_details_' . str_replace(' ', '_', $worker1_key) . '_' . str_replace('/', '-', $formattedDate) . '.pdf';
        log_message("Generated file name: $fileName");

        // Save the PDF
        $outputFile = __DIR__ . '/' . $fileName; // Ensure the path is correctly set
        $pdf->Output($outputFile, 'F');

        log_message("PDF generated successfully and saved to $outputFile");

        // Generate JSON
        $jsonFilename = 'shift_report_' . time() . '.json';
        generateJSON($formData, $jsonFilename);

        // Save form data to database
        if (isset($formData['id']) && !empty($formData['id'])) {
            $id = $formData['id'];
            syncFiles($id, $formData);
        } else {
            $id = saveFormData($formData);
        }

        // Implement Signal API to send PDF and JSON to workers (pseudo-code)
        // send_to_signal($outputFile, $jsonFilename, $worker1, $worker2);

        // Return response
        echo json_encode([
            'status' => 'success',
            'message' => 'Shift details processed successfully',
            'pdf_url' => basename($outputFile),
            'json_url' => basename($jsonFilename),
            'id' => $id
        ]);
    } catch (Exception $e) {
        log_message("Error generating PDF: " . $e->getMessage());
        echo json_encode([
            'status' => 'error',
            'message' => 'Error generating PDF: ' . $e->getMessage()
        ]);
    }
}
?>
