<?php
//----- Database Connection & Content Type Declaration -----//
header('Content-Type: application/json');
require_once "../db/functions.php";
$conn = db_connect();
//----- Count Won/Lost Leads Per Year Function -----//
$month = isset($_GET['month']) && $_GET['month'] != '' ? intval($_GET['month']) : null;
$year = isset($_GET['year']) && $_GET['year'] != '' ? intval($_GET['year']) : null;
$whereClause = "WHERE ps.status_name IN ('Closed - Won', 'Closed - Lost', 'Client Onboarding')";
$params = [];
if ($month !== null) {$whereClause .= " AND MONTH(pm.prospect_date) = ?"; $params[] = $month;}
if ($year !== null) {$whereClause .= " AND YEAR(pm.prospect_date) = ?";$params[] = $year;}
$sql = "SELECT ps.status_name, 
            COUNT(pm.prospect_id) AS count 
        FROM prospect_monitor pm 
        JOIN prospect_statuses ps 
        ON pm.status_id = ps.status_id $whereClause 
        GROUP BY ps.status_name";
$stmt = $conn->prepare($sql);
$types = str_repeat('i', count($params));
if (!empty($params)) {$stmt->bind_param($types, ...$params);}
$stmt->execute();
$result = $stmt->get_result();
$data = array('closed - won' => 0, 'closed - lost' => 0, 'client onboarding' => 0);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $status = strtolower($row['status_name']);
        $data[$status] = intval($row['count']);
    }
}
echo json_encode($data, JSON_NUMERIC_CHECK);
$stmt->close();
$conn->close();
?>