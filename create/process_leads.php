<?php
//----- Database Connection -----//
require_once '../db/functions.php';
//----- Retrieve Leads Input Data Using POST -----//
if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $conn = db_connect();
        if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    $client_name = isset($_POST["client_name"]) ? sanitize_input($_POST["client_name"]) : '';
    $service_id = isset($_POST["service_id"]) ? sanitize_input($_POST["service_id"]) : 10;
    $service_remarks = isset($_POST["prospect_service_remarks"]) ? sanitize_input($_POST["prospect_service_remarks"]) : '';
    $client_location = isset($_POST["client_location"]) ? sanitize_input($_POST["client_location"]) : '';
    $region_id = isset($_POST["region_id"]) ? sanitize_input($_POST["region_id"]) : 1;
    $prospect_date = isset($_POST["prospect_date"]) ? sanitize_input($_POST["prospect_date"]) : '';
    $status_id = isset($_POST["status_id"]) ? sanitize_input($_POST["status_id"]) : 1;
    $status_remarks = isset($_POST["prospect_status_remarks"]) ? sanitize_input($_POST["prospect_status_remarks"]) : '';
    $prospect_reason = isset($_POST["prospect_reason"]) ? sanitize_input($_POST["prospect_reason"]) : '';
    $notice_date = isset($_POST["prospect_notice_date"]) ? sanitize_input($_POST["prospect_notice_date"]) : '';
    $notice_to = isset($_POST["prospect_notice_to"]) ? sanitize_input($_POST["prospect_notice_to"]) : '';
    $month_est = isset($_POST["prospect_month_est"]) ? sanitize_input($_POST["prospect_month_est"]) : '';
    $contract_sign = isset($_POST["prospect_contract_sign"]) ? sanitize_input($_POST["prospect_contract_sign"]) : '';
    $contract_status_id = isset($_POST["contract_status_id"]) ? sanitize_input($_POST["contract_status_id"]) : 3;
    $contract_period = isset($_POST["prospect_contract_period"]) ? sanitize_input($_POST["prospect_contract_period"]) : '';
    $contract_start = isset($_POST["prospect_contract_start"]) ? sanitize_input($_POST["prospect_contract_start"]) : '';
    $contract_end = isset($_POST["prospect_contract_end"]) ? sanitize_input($_POST["prospect_contract_end"]) : '';
    $contract_remarks = isset($_POST["prospect_contract_remarks"]) ? sanitize_input($_POST["prospect_contract_remarks"]) : '';
    $client_rep = isset($_POST["client_rep"]) ? sanitize_input($_POST["client_rep"]) : '';
    $client_email = isset($_POST["client_email"]) ? sanitize_input($_POST["client_email"]) : '';
    $client_phone_num = isset($_POST["client_phone_num"]) ? sanitize_input($_POST["client_phone_num"]) : '';
//----- Insert/Bind Clients Input Data -----//
    $client_sql = "INSERT INTO clients (client_name, client_location, client_rep, client_email, client_phone_num, region_id) VALUES (?, ?, ?, ?, ?, ?)";
    $client_stmt = $conn->prepare($client_sql);
        if ($client_stmt === false) {die("Error preparing client statement: " . $conn->error);}
    $client_stmt->bind_param("ssssis", $client_name, $client_location, $client_rep, $client_email, $client_phone_num, $region_id);
        if (!$client_stmt->execute()) {die("Error inserting client: " . $client_stmt->error);}
    $client_id = $client_stmt->insert_id;
    $client_stmt->close();
//----- Insert/Bind Prospects Input Data -----//
    $prospect_sql = "INSERT INTO prospect_monitor (client_id, service_id, prospect_service_remarks, prospect_date,
        status_id, prospect_status_remarks, prospect_reason, prospect_notice_date, prospect_notice_to,
        prospect_month_est, prospect_contract_sign, prospect_contract_period, prospect_contract_start,
        prospect_contract_end, prospect_contract_remarks, contract_status_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $prospect_stmt = $conn->prepare($prospect_sql);
        if ($prospect_stmt === false) {die("Error preparing prospect statement: " . $conn->error);}
    $prospect_stmt->bind_param("iississssssssssi",
        $client_id, $service_id, $service_remarks, $prospect_date, $status_id, $status_remarks,
        $prospect_reason, $notice_date, $notice_to, $month_est, $contract_sign,
        $contract_period, $contract_start, $contract_end, $contract_remarks, $contract_status_id
    );
        if (!$prospect_stmt->execute()) {die("Error inserting prospect: " . $prospect_stmt->error);}
    $prospect_id = $conn->insert_id;
    echo "success";
    $prospect_stmt->close();
    $conn->close();
} else {echo "Error: This script must be accessed via a POST request.";}
?>