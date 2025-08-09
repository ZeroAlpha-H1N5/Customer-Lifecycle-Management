<?php
//----- Database Connection & Composer DOMpdf Import -----//
require_once '../db/functions.php';
require_once '../vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;
//----- Fetch Meeting Data -----//
$meet_id = isset($_GET['meet_id']) ? (int)$_GET['meet_id'] : null;
if (!$meet_id) {
    die("Error: Missing meet_id parameter.");
}
function getMeetingData($meet_id) {
    $conn = db_connect();
    $sql = "SELECT
                mm.meet_id,
                mm.meet_date AS 'Date',
                c.client_name AS 'Client',
                mm.meet_source AS 'Source',
                mm.meet_issue AS 'Issue',
                mm.meet_action AS 'Action',
                GROUP_CONCAT(mm.meet_respo SEPARATOR ', ') AS responsible_parties,
                mm.meet_timeline AS 'Timeline',
                ms.status_name AS meet_status,
                mp.prio_name AS meet_priority,
                mm.meet_remarks
            FROM
                meeting_minutes mm
            JOIN
                clients c ON mm.client_id = c.client_id
            LEFT JOIN
                meet_status ms ON mm.meet_status_id = ms.status_id
            LEFT JOIN
                meet_prio mp ON mm.meet_prio_id = mp.prio_id
            WHERE
                mm.meet_id = " . $meet_id . "
            GROUP BY mm.meet_id";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $data = $result->fetch_assoc();
    } else {
        $data = null;
    }
    $conn->close();
    return $data;
}
//----- Generate Meetings HTML PDF -----//
function generateMeetingPDFHtml($data) {
    $date = htmlspecialchars($data['Date'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
    $client = htmlspecialchars($data['Client'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
    $source = htmlspecialchars($data['Source'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
    $status = htmlspecialchars($data['meet_status'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
    $responsible = htmlspecialchars($data['responsible_parties'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
    $issue = htmlspecialchars($data['Issue'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
    $action = htmlspecialchars($data['Action'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
    $remarks = htmlspecialchars($data['meet_remarks'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
    $timeline = htmlspecialchars($data['Timeline'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
    $prioritization = htmlspecialchars($data['meet_priority'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
    $logo_url = '/icons/safexpress_logo.png';
    $logo_path = $_SERVER['DOCUMENT_ROOT'] . $logo_url;
    $logoTag = '';
    if (file_exists($logo_path)) {
        $logoTag = '<img src="' . htmlspecialchars($logo_url) . '" alt="Company Logo" width="150">';
    } else {
        $logoTag = 'Logo not found: ' . htmlspecialchars($logo_path);
    }
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <title>Minutes of Meeting</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
        <style>
            body {font-family: Montserrat, sans-serif; line-height: 1.6;}
            .container {max-width: 1000px; margin: 0 ; padding: 0 10px;}
            h1 {text-align: center;}
            .header {display: flex; justify-content: flex-start; align-items: center; margin-bottom: 10px; gap: 10px;}
            .header img {max-height: 30px;}
            .logo {max-height: 50px;}
            .safexpress-text {font-size: 1.2em; font-weight: bold; color: #333; text-align: left;}
            .business-dev-text {font-size: 1em; color: #777; text-align: left;}
            .minutes-of-meetings-text {font-size: 1.5em; text-align: center; color: #333; margin-bottom: 5px;}
            table {width: 100% !important; border-collapse: collapse; margin-bottom: 10px;}
            th, td {padding: 8px; text-align: left; vertical-align: top;}
            label {font-weight: bold; display: block; margin-bottom: 5px;}
            input[type="text"], textarea {width: 100%; padding: 5px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-family: Montserrat, sans-serif; font-size: .8em; margin:0px;}
            #status, #responsible { height: 30px;}
            #timeline, #prioritization { height: 30px; }
          </style>
    </head>
    <body>
    <div class="container">
        <div class="header">
            <div>
              <img src="' . $logoTag . '" alt="Company Logo" width="150">
            </div>
            <div>
                <div class="safexpress-text">SAFEXPRESS LOGISTICS INC</div>
                <div class="business-dev-text">Business Development</div>
            </div>
        </div>
        <h1 class="minutes-of-meetings-text">Minutes of Meetings</h1>
        <table>
            <tr>
                <td scolspan="1">
                    <label for="date">Date:</label>
                    <input type="text" id="date" value="'. $date .'">
                </td>
                <td colspan="2">
                    <label for="client">Client:</label>
                    <input type="text" id="client" value="'. $client .'">
                </td>
            </tr>
            <tr>
                <td colspan="4">
                    <label for="source">Source:</label>
                    <input type="text" id="source" value="'. $source .'" ">
                </td>
            </tr>
            <tr>
                <td colspan="1">
                    <label for="status">Status:</label>
                    <textarea id="status">'. $status .'</textarea>
                </td>
                <td colspan="2">
                    <label for="responsible">Responsible:</label>
                    <textarea id="responsible" >'. $responsible .'</textarea>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="issue">Issue/s:</label>
                    <textarea id="issue" style="min-height:300px;" >'. $issue .'</textarea>
                </td>
                <td>
                    <label for="action_plan">Action Plan:</label>
                    <textarea id="action_plan" style="min-height:300px;">'. $action .'</textarea>
                </td>
                <td>
                    <label for="remarks">Remarks:</label>
                    <textarea id="remarks" style="min-height:300px;">'. $remarks .'</textarea>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="timeline">Timeline:</label>
                    <input type="text" id="timeline" value="'. $timeline .'">
                </td>
                <td colspan="4">
                    <label for="prioritization">Prioritization:</label>
                    <input type="text" id="prioritization" value="'. $prioritization .'">
                </td>
            </tr>
        </table>
    </div>
</body>
    </html>';
    return $html;
}
//----- Export Meetings To PDF Function -----//
$meetingData = getMeetingData($meet_id);
if (!$meetingData) {
    die("Error: Meeting data not found for meet_id = " . $meet_id);
}
$html = generateMeetingPDFHtml($meetingData);
$options = new Options();
$options->set('defaultFont', 'Arial');
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('Letter', 'portrait');
$dompdf->render();
$filename = 'SLI-BD-MOM-' . $meet_id . '.pdf';
$dompdf->stream($filename, array("Attachment" => 0));
?>