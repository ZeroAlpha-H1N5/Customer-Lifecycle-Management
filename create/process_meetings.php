<?php
//----- Database Connection -----//
require_once '../db/functions.php';
//----- Retrieve Leads Input Data Using POST -----//
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = db_connect();
        if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    $meet_date = sanitize_input($_POST['meet_date']);
    $meet_client_name = sanitize_input($_POST['meet_client_name']);
    $meet_source = sanitize_input($_POST['meet_source']);
    $meet_issue = sanitize_input($_POST['meet_issue']);
    $meet_action = sanitize_input($_POST['meet_action']);
        if (isset($_POST['respo'])) {$respos = $_POST['respo'];}
        else {$respos = array();}
    $meet_timeline = sanitize_input($_POST['meet_timeline']);
    $meet_status = sanitize_input($_POST['meet_status']);
    $meet_prio = sanitize_input($_POST['meet_prio']);
    $meet_remarks = sanitize_input($_POST['meet_remarks']);
//----- Retrieve Existing Clients Data -----//
    $client_stmt = $conn->prepare("SELECT client_id FROM clients WHERE client_name = ?");
    $client_stmt->bind_param("s", $meet_client_name);
    $client_stmt->execute();
    $client_result = $client_stmt->get_result();
    // Client exists, get the client_id
        if ($client_result->num_rows > 0) {$client_row = $client_result->fetch_assoc(); $client_id = $client_row['client_id'];}
        else {$insert_client_stmt = $conn->prepare("INSERT INTO clients (client_name) VALUES (?)"); $insert_client_stmt->bind_param("s", $meet_client_name);
    // Client does not exist, insert it
        if ($insert_client_stmt->execute() === TRUE) {$client_id = $conn->insert_id;}
        else {echo "Error inserting client: " . $conn->error; exit();} $insert_client_stmt->close();}
    $client_stmt->close();
    // Comma separation for respos
    $respo_string = implode(", ", $respos);
//----- Insert/Bind Meetings Input Data -----//
    $stmt = $conn->prepare("INSERT INTO meeting_minutes (meet_date, client_id, meet_source, meet_issue, meet_action, meet_timeline, meet_status_id, meet_prio_id, meet_remarks, meet_respo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssiiss", $meet_date, $client_id, $meet_source, $meet_issue, $meet_action, $meet_timeline, $meet_status, $meet_prio, $meet_remarks, $respo_string);
        if ($stmt->execute() === TRUE) {echo "New meeting created successfully";}
        else {echo "Error: " . $conn->error;}
    echo "success";
    $stmt->close();
    $conn->close();
} else {echo "Error: This script must be accessed via a POST request.";}
?>