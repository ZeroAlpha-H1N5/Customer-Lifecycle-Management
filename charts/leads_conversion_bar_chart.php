<?php
//----- Database Connection & Content Type Declaration -----//
header('Content-Type: application/json');
require_once "../db/functions.php";
$conn = db_connect();
//----- Count Leads Conversion Per Year Function -----//
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$year = filter_var($year, FILTER_SANITIZE_NUMBER_INT);
$sql = "SELECT
            MONTH(pm.prospect_date) AS month,
            COUNT(CASE
                WHEN ps.status_name IN ('Closed - Won', 'On Hold', 'Client Onboarding', 'Repeat Business Opportunity')
                THEN pm.prospect_id
            END) AS favorable_count,
            (SELECT COUNT(prospect_id) FROM prospect_monitor WHERE YEAR(prospect_date) = $year) AS total_count_overall
        FROM prospect_monitor pm
        JOIN prospect_statuses ps ON pm.status_id = ps.status_id
        WHERE YEAR(pm.prospect_date) = $year
        GROUP BY MONTH(pm.prospect_date)
        ORDER BY MONTH(pm.prospect_date);";
if ($result = $conn->query($sql)) {
    if ($result->num_rows > 0) {
        $data = array();
        while ($row = $result->fetch_assoc()) {
            $month = $row['month'];
            $favorable_count = (int)$row['favorable_count'];
            $total_count = (int)$row['total_count_overall'];
            $conversion_ratio = 0;
            if ($total_count > 0) {
                $conversion_ratio = ($favorable_count / $total_count) * 100;
            }
            $data[] = array(
                'month' => $month,
                'conversion_ratio' => round($conversion_ratio, 2)
            );
        }
        header('Content-Type: application/json');
        http_response_code(200);
        echo json_encode($data, JSON_NUMERIC_CHECK);
    } else {
        header('Content-Type: application/json');
        http_response_code(204);
        echo "[]";
    }
    $result->free();
} else {
    header('Content-Type: application/json');
    http_response_code(500);
    $error = array('error' => $conn->error);
    echo json_encode($error);
}
$conn->close();
?>