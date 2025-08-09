<?php
//----- Database Connection -----//
require_once '../db/functions.php';
//----- Fetch Contracts Data -----//
function getClientOnboardingData() {
    $conn = db_connect();
    $sql = "SELECT
            c.client_name,
            c.client_rep,
            c.client_email,
            c.client_location,
            c.client_phone_num,
            r.region_name,
            s.service_name,
            pm.prospect_service_remarks,
            pm.prospect_date,
            pm.prospect_notice_date,
            pm.prospect_notice_to,
            pm.prospect_month_est,
            pm.prospect_contract_sign,
            pm.prospect_contract_period,
            pm.prospect_contract_start,
            pm.prospect_contract_end,
            pm.prospect_contract_remarks,
            cs.contract_status_name
        FROM
            prospect_monitor pm
        JOIN
            clients c ON pm.client_id = c.client_id
        JOIN
            services s ON pm.service_id = s.service_id
        JOIN
            prospect_statuses ps ON pm.status_id = ps.status_id
        LEFT JOIN
            region r ON c.region_id = r.region_id
        LEFT JOIN
            contract_statuses cs ON pm.contract_status_id = cs.contract_status_id
        WHERE ps.status_id = 14";
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
//----- Export Contracts Table To CSV Function -----//
$data = getClientOnboardingData();
if (count($data) == 0) {
    echo "<script>alert('No data to export.'); window.location.href='../contract_monitoring.php';</script>";
    exit;
}
$filename = "SLI_CONTRACT_MONITORING.csv";
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');
$output = fopen('php://output', 'w');
fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
$header = array(
    'Client Name',
    'Client Representative',
    'Email Address',
    'Phone Number',
    'City/Municipality',
    'Region',
    'Project',
    'Specific Service',
    'Prospect Date',
    'Notice Date',
    'Notice To',
    'Month Est.',
    'Contract Sign Date',
    'Contract Status',
    'Contract Period',
    'Contract Start',
    'Contract End',
    'Contract Remarks'
);
fputcsv($output, $header);
foreach ($data as $row) {
   $rowData = array(
        $row['client_name'],
        $row['client_rep'],
        $row['client_email'],
        $row['client_phone_num'],
        $row['client_location'],
        $row['region_name'],
        $row['service_name'],
        $row['prospect_service_remarks'],
        $row['prospect_date'],
        $row['prospect_notice_date'],
        $row['prospect_notice_to'],
        $row['prospect_month_est'],
        $row['prospect_contract_sign'],
        $row['contract_status_name'],
        $row['prospect_contract_period'],
        $row['prospect_contract_start'],
        $row['prospect_contract_end'],
        $row['prospect_contract_remarks']
   );
    fputcsv($output, $rowData);
}
fclose($output);
exit;
?>