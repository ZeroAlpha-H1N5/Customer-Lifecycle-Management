<?php
//----- Database Connection & Content Type Declaration -----//
header('Content-Type: application/json');
require_once "../db/functions.php";
$conn = db_connect();
//----- Count Prospect Per Status Function -----//
$sql = "SELECT ps.status_name, COUNT(pm.prospect_id) AS prospect_count
        FROM prospect_monitor pm
        JOIN prospect_statuses ps ON pm.status_id = ps.status_id
        GROUP BY ps.status_name";

$result = $conn->query($sql);
if ($result) {
    $data = array();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    header('Content-Type: application/json');
    echo json_encode($data, JSON_NUMERIC_CHECK);
} else {
    header('Content-Type: application/json');
    http_response_code(500);
    $error = array('error' => $conn->error);
    echo json_encode($error);
}
$conn->close();
?>