<?php
//----- Database Connection -----//
require_once '../db/functions.php';
//----- Retrieve Fairs Input Data Using POST -----//
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = db_connect();
        if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    $fair_title   = sanitize_input($_POST["fair_title"]);
    $fair_date_start = sanitize_input($_POST["fair_date_start"]);
    $fair_date_end   = sanitize_input($_POST["fair_date_end"]);
    $fair_venue      = sanitize_input($_POST["fair_venue"]);
    $fair_desc = sanitize_input($_POST["fair_desc"]);
    $fair_remarks = sanitize_input($_POST["fair_remarks"]);
        if (empty($fair_title)) {echo "Error: Fair Title required."; exit;}
//----- Insert/Bind Fairs Input Data -----//
    $sql = "INSERT INTO trade_fairs (fair_title, fair_date_start, fair_date_end, fair_venue, fair_desc, fair_remarks) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
        if ($stmt === false) {echo "Error preparing statement: " . $conn->error; exit;}
    $stmt->bind_param("ssssss", $fair_title, $fair_date_start, $fair_date_end, $fair_venue, $fair_desc, $fair_remarks);
        if ($stmt->execute()) {echo "success";} 
        else {echo "Error: " . $stmt->error;}
    $stmt->close();
    $conn->close();
} else {echo "Error: This script must be accessed via a POST request.";}
?>