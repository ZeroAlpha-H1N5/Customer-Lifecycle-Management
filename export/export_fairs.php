<?php
//----- Database Connection -----//
require_once '../db/functions.php';
//----- Fetch Trade Fairs Data -----//
function getTradeFairsData(
    $filter_field = null,
    $search_term = '',
    $sort_order = 'ASC'
) {
    $conn = db_connect();
    $sql = "SELECT
                    tf.fair_id,
                    tf.fair_title,
                    tf.fair_date_start,
                    tf.fair_date_end,
                    tf.fair_venue,
                    tf.fair_desc,
                    tf.fair_remarks
                FROM
                    trade_fairs tf";
    if ($filter_field && $search_term != '') {
        $sql .= " WHERE " . $filter_field . " LIKE '%" . $conn->real_escape_string($search_term) . "%'";
    }
    $sql .= " ORDER BY " . ($filter_field ? $filter_field : 'tf.fair_date_start') . " " . $sort_order;
    $result = $conn->query($sql);
    $data = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    $conn->close();
    return $data;
}
//----- Export Fairs Table to CSV Function -----//
$filter_field = $_GET['filter_field'] ?? null;
$search_term = $_GET['search_term'] ?? '';
$sort_order = $_GET['sort_order'] ?? 'ASC';
$data = getTradeFairsData($filter_field, $search_term, $sort_order);
if (count($data) == 0) {
    echo "<script>alert('No data to export.'); window.location.href='../upcoming_exhibit.php';</script>";
    exit;
}
$filename = "SLI_TRADE_FAIRS.csv";
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');
$output = fopen('php://output', 'w');
fwrite($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
$header = array("Fair ID", "Title", "Date Start", "Date End", "Venue", "Exhibit Description", "Remarks");
fputcsv($output, $header);
foreach ($data as $row) {
    $csv_row = array(
        $row['fair_id'],
        $row['fair_title'],
        $row['fair_date_start'],
        $row['fair_date_end'],
        $row['fair_venue'],
        $row['fair_desc'],
        $row['fair_remarks']
    );
    fputcsv($output, $csv_row);
}
fclose($output);
exit;
?>