<?php
//----- Database Connection & Composer Mailjet Importing -----//
require_once './../db/functions.php';
require './../vendor/autoload.php';
use Mailjet\Client;
use Mailjet\Resources;
//----- Mailjet Secret/API Key (KEEP IN A SAFE LOCATION) -----//
$apiKey = '71ad0677f378cb32cc9e0513cac8d574';
$apiSecret = 'df7caee7d4e29553b6b57f25c6432d88';
$senderEmail = 'sli.businessdev@gmail.com';
$senderName = 'Safexpress Logistics Inc.';
//----- Subject, Recipient Name and Email -----//
$emailSubject = 'Contract Expiry Summary Report';
$reportRecipientEmail = 'zeroalpha415@gmail.com';
$reportRecipientName = 'User';
function generateContractExpiryReport($forceAll = false) {
    $conn = db_connect();
    $client_onboarding_status_id = 14;
    $sql = "SELECT
                c.client_name,
                c.client_rep,
                c.client_email,
                c.client_location,
                c.client_phone_num,
                pm.prospect_contract_period,
                pm.prospect_contract_start,
                pm.prospect_contract_end,
                cs.contract_status_name
            FROM prospect_monitor pm
            JOIN clients c ON pm.client_id = c.client_id
            JOIN prospect_statuses ps ON pm.status_id = ps.status_id
            LEFT JOIN contract_statuses cs ON pm.contract_status_id = cs.contract_status_id
            WHERE ps.status_id = ?";
//----- Date Constraint Function -----//
    if ($forceAll) {
        $sql .= " AND pm.prospect_contract_end <= DATE_ADD(NOW(), INTERVAL 1 MONTH) 
                  AND pm.prospect_contract_end >= NOW()
                  ORDER BY prospect_contract_end ASC";
    }
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {error_log("Send Email: Error preparing statement: " . $conn->error); return false;}
    if ($forceAll) {$stmt->bind_param("i", $client_onboarding_status_id);}
    else {$stmt->bind_param("i", $client_onboarding_status_id);}
    $stmt->execute();
    $result = $stmt->get_result();
    $reportData = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {$reportData[] = $row;}
    }
    $stmt->close();
    $conn->close();
    return $reportData;
}
//----- Generate Email Report Function -----//
function sendContractExpiryReportEmail($reportData) {
    global $apiKey, $apiSecret, $senderEmail, $senderName, $emailSubject, $reportRecipientEmail, $reportRecipientName;
    $success = true;
    $message = "";
    $htmlReport = "<h2>Expiring Contracts Summary</h2>";
    if (empty($reportData)) {
        $htmlReport .= "<p>No contracts expiring within one month.</p>";
        $message = "No contracts expiring within one month.";
    } else {
    // HTML Email Report Format
        $htmlReport .= "<p>The following contracts are expiring:</p>";
        $htmlReport .= "<table border='1'>";
        $htmlReport .= "<tr>
        <th>Client Name</th>
        <th>Client Representative</th>
        <th>Email</th>
        <th>Phone Number</th>
        <th>City/Municipality</th>
        <th>Contract Period</th>
        <th>Contract Start Date</th>
        <th>Contract End Date</th>
        <th>Contract Status</th></tr>";
        foreach ($reportData as $contract) {
            $htmlReport .= "<tr>";
            $htmlReport .= "<td>" . htmlspecialchars($contract['client_name']) . "</td>";
            $htmlReport .= "<td>" . htmlspecialchars($contract['client_rep']) . "</td>";
            $htmlReport .= "<td>" . htmlspecialchars($contract['client_email']) . "</td>";
            $htmlReport .= "<td>" . htmlspecialchars($contract['client_phone_num']) . "</td>";
            $htmlReport .= "<td>" . htmlspecialchars($contract['client_location']) . "</td>";
            $htmlReport .= "<td>" . htmlspecialchars($contract['prospect_contract_period']) . "</td>";
            $htmlReport .= "<td>" . htmlspecialchars($contract['prospect_contract_start']) . "</td>";
            $htmlReport .= "<td>" . htmlspecialchars($contract['prospect_contract_end']) . "</td>";
            $htmlReport .= "<td>" . htmlspecialchars($contract['contract_status_name']) . "</td>";
            $htmlReport .= "</tr>";
        }
        $htmlReport .= "</table>";
        $message = "Contract Expiry Summary Report generated and sent.";
    }
    //Plaintext Email Report Format
    $textReport = "Expiring Contracts Summary:\n\n";
    if (empty($reportData)) {$textReport .= "No contracts expiring within one month.\n";} 
    else {
        $textReport .= "The following contracts are expiring:\n\n";
        foreach ($reportData as $contract) {
            $textReport .= "Client Name: " . $contract['client_name'] . "\n";
            $textReport .= "Client Representative: " . $contract['client_rep'] . "\n";
            $textReport .= "Email: " . $contract['client_email'] . "\n";
            $textReport .= "Phone Number: " . $contract['client_phone_num'] . "\n";
            $textReport .= "City/Municipality: " . $contract['client_location'] . "\n";
            $textReport .= "Contract Period: " . $contract['prospect_contract_period'] . "\n";
            $textReport .= "Contract Start Date: " . $contract['prospect_contract_start'] . "\n";
            $textReport .= "Contract End Date: " . $contract['prospect_contract_end'] . "\n";
            $textReport .= "Contract Status: " . $contract['contract_status_name'] . "\n";
        }
    }
//----- Send Email Report Function -----//
    $mj = new Client($apiKey, $apiSecret, true, ['version' => 'v3.1']);
    $body = [
        'Messages' => [
            [
                'From' => [
                    'Email' => $senderEmail,
                    'Name' => $senderName
                ],
                'To' => [
                    [
                        'Email' => $reportRecipientEmail,
                        'Name' => $reportRecipientName
                    ]
                ],
                'Subject' => $emailSubject,
                'TextPart' => $textReport,
                'HTMLPart' => $htmlReport,
                'CustomID' => 'ContractExpirySummaryReport'
            ]
        ]
    ];
    try {
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        if ($response->success()) {error_log("Send Email: Contract Expiry Summary Report sent successfully to " . $reportRecipientEmail);}
        else {
            error_log("Send Email: Error sending Contract Expiry Summary Report to " . $reportRecipientEmail . ": " . print_r($response->getData(), true));
            $success = false;
            $message = "Send Email: Error sending Contract Expiry Summary Report. Check the logs.";
        }
    } catch (\Exception $e) {
        error_log("Send Email:  Exception sending Contract Expiry Summary Report to " . $reportRecipientEmail . ": " . $e->getMessage());
        $success = false;
        $message = "Send Email: Exception sending Contract Expiry Summary Report: " . $e->getMessage();
    }
    return ['success' => $success, 'message' => $message];
}
//----- Force Send Email Report Button Function -----//
$forceSend = isset($_POST['force_send']) && $_POST['force_send'] == '1';
$reportData = generateContractExpiryReport($forceSend);
$emailResult = sendContractExpiryReportEmail($reportData);
$success = $emailResult['success'];
$message = $emailResult['message'];
$redirectUrl = "../contract_monitoring.php?email_success=" . ($success ? '1' : '0');
header("Location: " . $redirectUrl);
exit;
?>