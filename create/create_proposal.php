<?php
//----- Database Connection & Access Validation -----//
require_once '../db/functions.php';
if ($_SERVER["REQUEST_METHOD"] != "POST") {echo "Invalid request method."; exit;}
//----- Verify Prospect ID -----//
if (!isset($_POST["prospect_id"]) || !is_numeric($_POST["prospect_id"])) {echo "Invalid prospect ID."; exit;}
$prospect_id = intval($_POST["prospect_id"]);
$conn = db_connect();
//----- Duplicate Validation -----//
$check_sql = "SELECT COUNT(*) FROM proposal_monitor WHERE prospect_id = ?";
$check_stmt = $conn->prepare($check_sql);
if ($check_stmt === false) {
    echo "Error preparing check statement: " . $conn->error;
    $conn->close();
    exit;
}
$check_stmt->bind_param("i", $prospect_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$row = $check_result->fetch_row();
$proposal_count = $row[0];
$check_stmt->close();
if ($proposal_count > 0) {
    echo "A proposal already exists for this prospect.";
    $conn->close();
    exit;
}
$sql_fetch_ids = "SELECT client_id, service_id FROM prospect_monitor WHERE prospect_id = ?";
$stmt_fetch_ids = $conn->prepare($sql_fetch_ids);
if ($stmt_fetch_ids === false) {
    echo "Error preparing fetch statement: " . $conn->error;
    $conn->close();
    exit;
}
$stmt_fetch_ids->bind_param("i", $prospect_id);
$stmt_fetch_ids->execute();
$result_fetch_ids = $stmt_fetch_ids->get_result();
if ($result_fetch_ids->num_rows != 1) {
    echo "Prospect not found or invalid prospect ID.";
    $stmt_fetch_ids->close();
    $conn->close();
    exit;
}
$row_fetch_ids = $result_fetch_ids->fetch_assoc();
$client_id = $row_fetch_ids["client_id"];
$service_id = $row_fetch_ids["service_id"];
$stmt_fetch_ids->close();
//----- Generate Series Number -----//
$prop_series_num = '';
$currentYear = date("Y");
$sql = "SELECT prop_series_num FROM proposal_monitor ORDER BY prop_id DESC LIMIT 1";
$result = $conn->query($sql);
if ($result && $row = $result->fetch_assoc()) {
    $last_series = $row['prop_series_num'];
    if ($last_series !== null && $last_series !== "N/A") {
        $parts = explode('-', $last_series);
        $last_seq = intval(end($parts));
        $next_seq = $last_seq + 1;
        $prop_series_num = sprintf("QTN-%s-%03d", $currentYear, $next_seq);
    } else {
        $prop_series_num = sprintf("QTN-%s-%03d", $currentYear, 1);
    }
} else {
    $prop_series_num = sprintf("QTN-%s-%03d", $currentYear, 1);
}
//----- Create Proposal -----//
$proposal_sql = "INSERT INTO proposal_monitor (prospect_id, client_id, service_id, prop_series_num, prop_sent_by, proposal_status_id) VALUES (?, ?, ?, ?, '', 1)";
$proposal_stmt = $conn->prepare($proposal_sql);
if ($proposal_stmt === false) {
    echo "Error preparing proposal statement: " . $conn->error;
    $conn->close();
    exit;
}
$proposal_stmt->bind_param("iiis", $prospect_id, $client_id, $service_id, $prop_series_num);
if (!$proposal_stmt->execute()) {
    echo "Error inserting blank proposal: " . $proposal_stmt->error;
    $proposal_stmt->close();
    $conn->close();
    exit;
}
$proposal_stmt->close();
$conn->close();
echo "success";
?>