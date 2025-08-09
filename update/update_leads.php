<?php
//----- Database Connection -----//
require_once "../db/functions.php";
//----- Retrieve Updated Leads Input Data Using POST -----//
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = db_connect();
        if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    $prospect_id = isset($_POST["prospect_id"]) ? ($_POST["prospect_id"]) : '';
    $client_name = isset($_POST["client_name"]) ? ($_POST["client_name"]) : '';
    $service_id = isset($_POST["service_id"]) ? ($_POST["service_id"]) : 10;
    $service_remarks = isset($_POST["prospect_service_remarks"]) ? ($_POST["prospect_service_remarks"]) : '';
    $client_location = isset($_POST["client_location"]) ? ($_POST["client_location"]) : '';
    $prospect_date = isset($_POST["prospect_date"]) ? ($_POST["prospect_date"]) : '';
    $status_id = isset($_POST["status_id"]) ? ($_POST["status_id"]) : 1;
    $status_remarks = isset($_POST["prospect_status_remarks"]) ? ($_POST["prospect_status_remarks"]) : '';
    $prospect_reason = isset($_POST["prospect_reason"]) ? ($_POST["prospect_reason"]) : '';
    $notice_date = isset($_POST["prospect_notice_date"]) ? ($_POST["prospect_notice_date"]) : '';
    $notice_to = isset($_POST["prospect_notice_to"]) ? ($_POST["prospect_notice_to"]) : '';
    $month_est = isset($_POST["prospect_month_est"]) ? ($_POST["prospect_month_est"]) : '';
    $contract_sign = isset($_POST["prospect_contract_sign"]) ? ($_POST["prospect_contract_sign"]) : '';
    $contract_period = isset($_POST["prospect_contract_period"]) ? ($_POST["prospect_contract_period"]) : '';
    $contract_start = isset($_POST["prospect_contract_start"]) ? ($_POST["prospect_contract_start"]) : '';
    $contract_end = isset($_POST["prospect_contract_end"]) ? ($_POST["prospect_contract_end"]) : '';
    $contract_status_id = isset($_POST["contract_status_id"]) ? ($_POST["contract_status_id"]) : 3;
    $contract_remarks = isset($_POST["prospect_contract_remarks"]) ? ($_POST["prospect_contract_remarks"]) : '';
    $client_rep = isset($_POST["client_rep"]) ? ($_POST["client_rep"]) : '';
    $client_email = isset($_POST["client_email"]) ? ($_POST["client_email"]) : '';
    $client_phone_num = isset($_POST["client_phone_num"]) ? ($_POST["client_phone_num"]) : '';
    $region_id = isset($_POST["region_id"]) ? ($_POST["region_id"]) : '';
//----- Get Clients Data If Exists -----//
    $getClientId_sql = "SELECT client_id FROM prospect_monitor WHERE prospect_id = ?";
    $getClientId_stmt = $conn->prepare($getClientId_sql);
        if ($getClientId_stmt === false) {die("Error preparing client_id statement: " . $conn->error);}
    $getClientId_stmt->bind_param("i", $prospect_id);
    $getClientId_stmt->execute();
    $getClientId_result = $getClientId_stmt->get_result();
        if ($getClientId_result && $getClientId_result->num_rows == 1) {
            $getClientId_row = $getClientId_result->fetch_assoc();
            $client_id = $getClientId_row["client_id"];
        } else {die("Could not retrieve client ID");}
    $getClientId_stmt->close();
//----- Update Clients Input Data -----//
    $client_sql = "UPDATE clients SET client_name = ?, client_rep = ?, client_email = ?, client_phone_num = ?, client_location = ?, region_id = ? WHERE client_id = ?";
    $client_stmt = $conn->prepare($client_sql);
        if ($client_stmt === false) {die("Error preparing client statement: " . $conn->error);}
    $client_stmt->bind_param("sssssii", $client_name, $client_rep, $client_email, $client_phone_num, $client_location, $region_id, $client_id);
        if (!$client_stmt->execute()) {die("Error updating client: " . $client_stmt->error);}
    $client_stmt->close();
//----- Update/Bind Prospects Input Data -----//
    $sql = "UPDATE prospect_monitor SET
            service_id = ?, prospect_service_remarks = ?, prospect_date = ?, status_id = ?,
            prospect_status_remarks = ?, prospect_reason = ?, prospect_notice_date = ?, prospect_notice_to = ?,
            prospect_month_est = ?, prospect_contract_sign = ?, prospect_contract_period = ?, prospect_contract_start = ?,
            prospect_contract_end = ?, prospect_contract_remarks = ?, contract_status_id = ?
            WHERE prospect_id = ?";
    $stmt = $conn->prepare($sql);
        if ($stmt === false) {die("Error preparing statement: " . $conn->error);}
    $stmt->bind_param("isssssssssssssii",
        $service_id, $service_remarks, $prospect_date, $status_id, $status_remarks,
        $prospect_reason, $notice_date, $notice_to, $month_est, $contract_sign,
        $contract_period, $contract_start, $contract_end, $contract_remarks,
        $contract_status_id, $prospect_id
    );
    echo 'success';
    $stmt->close();
    $conn->close();
} else {echo "Error: This script must be accessed via a POST request.";}
?>