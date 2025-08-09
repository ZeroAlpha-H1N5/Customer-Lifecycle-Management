<?php
//----- Database Connection & Content Type Declaration -----//
header('Content-Type: application/json');
require_once "../db/functions.php";
$conn = db_connect();
//----- Retrieve Updated Fairs Input Data Using POST -----//
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fair_id = isset($_POST['fair_id']) && is_numeric($_POST['fair_id']) ? (int)$_POST['fair_id'] : 0;
    $fair_title = isset($_POST['fair_title']) ? trim($_POST['fair_title']) : '';
    $fair_date_start = isset($_POST['fair_date_start']) ? trim($_POST['fair_date_start']) : '';
    $fair_date_end = isset($_POST['fair_date_end']) ? trim($_POST['fair_date_end']) : '';
    $fair_venue = isset($_POST['fair_venue']) ? trim($_POST['fair_venue']) : '';
    $fair_desc = isset($_POST['fair_desc']) ? trim($_POST['fair_desc']) : '';
    $fair_remarks = isset($_POST['fair_remarks']) ? trim($_POST['fair_remarks']) : ''; 
//----- Update Fairs Input Data -----//
    $sql = "UPDATE trade_fairs SET fair_title = ?, fair_date_start = ?, fair_date_end = ?, fair_venue = ?, fair_desc = ?, fair_remarks = ? WHERE fair_id = ?";
    $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            echo json_encode(array('success' => false, 'message' => "Update Fairs: Error preparing statement: " . $conn->error));
            $conn->close();
            exit;
        }
    $stmt->bind_param("ssssssi", $fair_title, $fair_date_start, $fair_date_end, $fair_venue, $fair_desc, $fair_remarks, $fair_id);
        if ($stmt->execute()) {echo json_encode(array('success' => true));} 
        else {echo json_encode(array('success' => false, 'message' => "Update Fairs: Error updating record: " . $stmt->error));}
    $stmt->close();
    $conn->close();
} else {echo json_encode(array('success' => false, 'message' => 'Error: This script must be accessed via a POST request.'));}
?>