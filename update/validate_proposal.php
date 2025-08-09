<?php
//----- Database Connection -----//
require_once "functions.php";
//----- Validate User Action -----//
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        echo "Error: This script must be accessed via a POST request.";
        exit;
    }
    if (!isset($_POST["prospect_id"]) || !is_numeric($_POST["prospect_id"])) {
        echo "Invalid prospect ID.";
        exit;
    }
$prospect_id = intval($_POST["prospect_id"]);
$conn = db_connect();
//----- Check If Proposal Exists on a Specific Prospect -----//
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
$conn->close();
    if ($proposal_count > 0) {echo "A Proposal Already Exist for this Prospect.";}
    else {echo "Prospect Marked Successfully.";}
?>