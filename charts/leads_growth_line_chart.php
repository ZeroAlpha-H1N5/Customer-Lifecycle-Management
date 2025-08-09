<?php
//----- Database Connection & Content Type Declaration -----//
header('Content-Type: application/json');
require_once "../db/functions.php";
$conn = db_connect();
//----- Count Leads Growth Per Year Function -----//
$year = isset($_GET['year']) && $_GET['year'] != '' ? intval($_GET['year']) : null;
$params = [];
$sql = "SELECT MONTH(pm.prospect_date) AS month, 
            COUNT(pm.prospect_id) AS prospect_count
        FROM prospect_monitor pm
        JOIN prospect_statuses ps 
        ON pm.status_id = ps.status_id";
if ($year !== null) {$sql .= " WHERE YEAR(pm.prospect_date) = ?"; $params[] = $year;}
$sql .= " GROUP BY MONTH(pm.prospect_date) ORDER BY MONTH(pm.prospect_date)";
$stmt = $conn->prepare($sql);
$types = str_repeat('i', count($params));
if (!empty($params)) {$stmt->bind_param($types, ...$params);}
$stmt->execute();
$result = $stmt->get_result();
$data = array();
while ($row = $result->fetch_assoc()) {$data[] = $row;}
echo json_encode($data, JSON_NUMERIC_CHECK);
$stmt->close();
$conn->close();
?>