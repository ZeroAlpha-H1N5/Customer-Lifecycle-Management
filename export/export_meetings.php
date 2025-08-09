<?php
//----- Database Connection -----//
require_once '../db/functions.php';
//----- Fetch Meeting Minutes Data -----//
function getMeetingMinutesData(
    $filter_field = null,
    $search_term = '',
    $sort_order = 'ASC'
) {
    $conn = db_connect();
    $sql = "SELECT
                mm.meet_id,
                mm.meet_date AS 'Date',
                c.client_name AS 'Client',
                mm.meet_source AS 'Source',
                mm.meet_issue AS 'Issue',
                mm.meet_action AS 'Action',
                GROUP_CONCAT(mm.meet_respo SEPARATOR ', ') AS responsible_parties,
                mm.meet_timeline AS 'Timeline',
                ms.status_name AS meet_status,
                mp.prio_name AS meet_priority,
                mm.meet_remarks
            FROM
                meeting_minutes mm
            JOIN
                clients c ON mm.client_id = c.client_id
            LEFT JOIN
                meet_status ms ON mm.meet_status_id = ms.status_id
            LEFT JOIN
                meet_prio mp ON mm.meet_prio_id = mp.prio_id";
    if ($filter_field && $search_term != '') {
        $sql .= " WHERE " . $filter_field . " LIKE '%" . $conn->real_escape_string($search_term) . "%'";
    }
    $sql .= " GROUP BY mm.meet_id";
    $sql .= " ORDER BY " . ($filter_field ? $filter_field : 'mm.meet_date') . " " . $sort_order;
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
//----- Export Meetings to CSV Function -----//
$filter_field = $_GET['filter_field'] ?? null;
$search_term = $_GET['search_term'] ?? '';
$sort_order = $_GET['sort_order'] ?? 'ASC';
$data = getMeetingMinutesData($filter_field, $search_term, $sort_order);
if (count($data) == 0) {
    echo "<script>alert('No data to export.'); window.location.href='../meeting_minutes.php';</script>";
    exit;
}
$filename = "SLI_MINUTES_OF_MEETINGS.csv";
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');
$output = fopen('php://output', 'w');
fwrite($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
$header = array("Meeting ID", "Date", "Client", "Source", "Issue", "Action Plan", "Responsible Parties", "Timeline", "Status", "Priority", "Remarks");
fputcsv($output, $header);
foreach ($data as $row) {
    $csv_row = array(
        $row['meet_id'],
        $row['Date'],
        $row['Client'],
        $row['Source'],
        $row['Issue'],
        $row['Action'],
        $row['responsible_parties'],
        $row['Timeline'],
        $row['meet_status'],
        $row['meet_priority'],
        $row['meet_remarks']
    );
    fputcsv($output, $csv_row);
}
fclose($output);
exit;
?>