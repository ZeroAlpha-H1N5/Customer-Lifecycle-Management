<?php
//----- Database Connection -----//
require_once '../db/functions.php';
//----- Retrieve Proposals Input Data Using POST -----//
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = db_connect();
        if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    $prop_id = isset($_POST["prop_id"]) ? sanitize_input($_POST["prop_id"]) : '';
    $prop_date_sent = isset($_POST["prop_date_sent"]) ? sanitize_input($_POST["prop_date_sent"]) : '';
    $prop_sent_by = isset($_POST["prop_sent_by"]) ? sanitize_input($_POST["prop_sent_by"]) : '';
    $proposal_status_id = isset($_POST["proposal_status_id"]) ? sanitize_input($_POST["proposal_status_id"]) : '';
    $prop_signed_date = isset($_POST["prop_signed_date"]) ? sanitize_input($_POST["prop_signed_date"]) : '';
    $prop_remarks = isset($_POST["prop_remarks"]) ? sanitize_input($_POST["prop_remarks"]) : '';
        if (!is_numeric($prop_id)) {echo "Error: Invalid proposal ID."; exit;}
//----- Retrieve Existing Prospects Data -----//
    $sql_get_prospect_id = "SELECT prospect_id FROM proposal_monitor WHERE prop_id = ?";
    $stmt_get_prospect_id = $conn->prepare($sql_get_prospect_id);
        if ($stmt_get_prospect_id === false) {echo "Error preparing statement to get prospect_id: " . $conn->error; $conn->close(); exit;}
    $stmt_get_prospect_id->bind_param("i", $prop_id);
    $stmt_get_prospect_id->execute();
    $result_get_prospect_id = $stmt_get_prospect_id->get_result();
        if ($result_get_prospect_id->num_rows !== 1) { echo "Error: Could not find prospect_id for proposal id: " . $prop_id; $stmt_get_prospect_id->close(); $conn->close(); exit;}
    $row_ids = $result_get_prospect_id->fetch_assoc();
    $prospect_id = $row_ids['prospect_id'];
    $stmt_get_prospect_id->close();
//----- Update Existing Blank Proposal Data -----//
    $sql = "UPDATE proposal_monitor SET prop_date_sent = ?, prop_sent_by = ?, proposal_status_id = ?, prop_signed_date = ?, prop_remarks = ? WHERE prop_id = ?";
    $stmt = $conn->prepare($sql);
        if ($stmt === false) {echo "Error preparing statement: " . $conn->error; $conn->close(); exit;}
    $stmt->bind_param("ssissi", $prop_date_sent, $prop_sent_by, $proposal_status_id, $prop_signed_date, $prop_remarks, $prop_id);
        if ($stmt->execute()) {echo "success";}
        else {echo "Error updating record: " . $stmt->error;}
    $stmt->close();
    $conn->close();
} else {echo "Error: This script must be accessed via a POST request.";}
?>