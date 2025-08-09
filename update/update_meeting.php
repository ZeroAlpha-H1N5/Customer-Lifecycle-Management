<?php
//----- Database Connection & Content Type Declaration -----//
header('Content-Type: application/json');
require_once "../db/functions.php";
//----- Retrieve Updated Meetings Input Data Using POST -----//
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = db_connect();
        if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    $meet_id = isset($_POST['meet_id']) && is_numeric($_POST['meet_id']) ? (int)$_POST['meet_id'] : 0;
    $meet_date = isset($_POST['meet_date']) ? trim($_POST['meet_date']) : '';
    $client_name = isset($_POST['client_name']) ? trim($_POST['client_name']) : '';
    $meet_source = isset($_POST['meet_source']) ? trim($_POST['meet_source']) : '';
    $meet_issue = isset($_POST['meet_issue']) ? trim($_POST['meet_issue']) : '';
    $meet_action = isset($_POST['meet_action']) ? trim($_POST['meet_action']) : '';
    $meet_timeline = isset($_POST['meet_timeline']) ? trim($_POST['meet_timeline']) : '';
    $meet_status_id = isset($_POST['meet_status']) && is_numeric($_POST['meet_status']) ? (int)$_POST['meet_status'] : 0;
    $meet_prio_id = isset($_POST['meet_prio']) && is_numeric($_POST['meet_prio']) ? (int)$_POST['meet_prio'] : 0;
    $meet_remarks = isset($_POST['meet_remarks']) ? trim($_POST['meet_remarks']) : '';
    $meet_respo = isset($_POST['meet_respo']) ? $_POST['meet_respo'] : [];
        if ($meet_id <= 0) {echo json_encode(array('success' => false, 'message' => 'Update Meetings: Invalid meeting ID')); exit;}
        if (empty($client_name)) {echo json_encode(array('success' => false, 'message' => 'Update Meetings: Client Name cannot be empty')); exit;}
//----- Retrieve Client ID -----//
    $getClientId_sql = "SELECT mm.client_id FROM meeting_minutes mm INNER JOIN clients c ON mm.client_id = c.client_id WHERE mm.meet_id = ?"; //Specify where client_id is from
    $getClientId_stmt = $conn->prepare($getClientId_sql);
    if ($getClientId_stmt === false) {
        echo json_encode(array('success' => false, 'message' => 'Update Meetings: Error preparing client_id statement: ' . $conn->error));
        $conn->close();
        exit;
    }
    $getClientId_stmt->bind_param("i", $meet_id);
    $getClientId_stmt->execute();
    $getClientId_result = $getClientId_stmt->get_result();
        if ($getClientId_result && $getClientId_result->num_rows == 1) {
            $getClientId_row = $getClientId_result->fetch_assoc();
            $client_id = $getClientId_row["client_id"];
        } else {
            echo json_encode(array('success' => false, 'message' => 'Could not retrieve client ID'));
            $conn->close();
            exit;
        }
    $getClientId_stmt->close();
//----- Update Clients Data -----//
    $client_sql = "UPDATE clients SET client_name = ? WHERE client_id = ?";
    $client_stmt = $conn->prepare($client_sql);
        if ($client_stmt === false) {
            echo json_encode(array('success' => false, 'message' => 'Update Meetings: Error preparing client statement: ' . $conn->error));
            $conn->close();
            exit;
        }
    $client_stmt->bind_param("si", $client_name, $client_id);
        if (!$client_stmt->execute()) {
            echo json_encode(array('success' => false, 'message' => 'Update Meetings: Error updating client: ' . $client_stmt->error));
            $conn->close();
            exit;
        }
    $client_stmt->close();
    //----- Update Meetings Data -----//
    $sql = "UPDATE meeting_minutes SET meet_date = ?, meet_source = ?, meet_issue = ?, meet_action = ?, meet_timeline = ?, meet_status_id = ?, meet_prio_id = ?, meet_remarks = ?, meet_respo = ? WHERE meet_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        echo json_encode(array('success' => false, 'message' => 'Update Meetings: Error preparing statement: ' . $conn->error));
        $conn->close();
        exit;
    }
    $respoString = implode(", ", $meet_respo);
    $stmt->bind_param("sssssiissi", $meet_date, $meet_source, $meet_issue, $meet_action, $meet_timeline, $meet_status_id, $meet_prio_id, $meet_remarks, $respoString, $meet_id);
        if ($stmt->execute()) {
            echo json_encode(array('success' => true));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Update Meetings: Error updating record: ' . $stmt->error));
        }
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(array('success' => false, 'message' => 'Error: This script must be accessed via a POST request.'));
}
?>