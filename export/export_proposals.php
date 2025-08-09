<?php
//----- Database Connection -----//
require_once '../db/functions.php';
//----- Fetch Proposals Data -----//
function getProposalMonitoringData(
    $filter_field = null,
    $search_term = '',
    $sort_order = 'ASC'
) {
    $conn = db_connect();
    $sql = "SELECT
                pm.prop_id,
                pm.prospect_id,
                c.client_name AS 'Company',
                c.client_location AS 'Location',
                c.client_phone_num AS 'Phone',
                r.region_name AS 'Region',
                s.service_name AS 'Service',
                prom.prospect_service_remarks AS 'ServiceRemarks',
                pm.prop_series_num AS 'SeriesNo.',
                IFNULL(c.client_email, '') AS 'EmailAddress',
                IFNULL(c.client_rep, '') AS 'ClientRepresentative',
                IFNULL(pm.prop_date_sent, '') AS 'DateSent',
                IFNULL(pm.prop_sent_by, '') AS 'SentBy',
                ps.proposal_status_name AS 'ProposalStatus',
                IFNULL(pm.prop_signed_date, '') AS 'SignedProposalDate',
                IFNULL(pm.prop_remarks, '') AS 'Remarks'
            FROM proposal_monitor pm
            INNER JOIN clients c ON pm.client_id = c.client_id
            INNER JOIN region r ON c.region_id = r.region_id
            INNER JOIN services s ON pm.service_id = s.service_id
            INNER JOIN prospect_monitor prom ON pm.prospect_id = prom.prospect_id
            INNER JOIN proposal_statuses ps ON pm.proposal_status_id = ps.proposal_status_id";
    if ($filter_field && $search_term != '') {
        $sql .= " WHERE " . $filter_field . " LIKE '%" . $conn->real_escape_string($search_term) . "%'";
    }
    $sql .= " ORDER BY " . ($filter_field ? $filter_field : 'pm.prop_id') . " " . $sort_order;
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
//----- Export Proposals to CSV Function -----//
$filter_field = $_GET['filter_field'] ?? null;
$search_term = $_GET['search_term'] ?? '';
$sort_order = $_GET['sort_order'] ?? 'ASC';
$data = getProposalMonitoringData($filter_field, $search_term, $sort_order);
if (count($data) == 0) {
    echo "<script>alert('No data to export.'); window.location.href='../proposal_monitoring.php';</script>";
    exit;
}
$filename = "SLI_PROPOSAL_MONITORING.csv";
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');
$output = fopen('php://output', 'w');
fwrite($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
$header = array(
    "Proposal ID",
    "Series No.",
    "Company",
    "Client Representative",
    "Email Address",
    "Phone Number",
    "Location",
    "Region",
    "Service",
    "Service Remarks",
    "Date Sent",
    "Sent By",
    "Proposal Status",
    "Signed Proposal Date",
    "Remarks"
);
fputcsv($output, $header);
foreach ($data as $row) {
    $csv_row = array(
        $row['prop_id'],
        $row['SeriesNo.'],
        $row['Company'],
        $row['ClientRepresentative'],
        $row['EmailAddress'],
        $row['Phone'],
        $row['Location'],
        $row['Region'],
        $row['Service'],
        $row['ServiceRemarks'],
        $row['DateSent'],
        $row['SentBy'],
        $row['ProposalStatus'],
        $row['SignedProposalDate'],
        $row['Remarks']
    );
    fputcsv($output, $csv_row);
}
fclose($output);
exit;
?>