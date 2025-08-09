<?php
//----- Database Connection -----//
require_once '../db/functions.php';
//----- Fetch Leads Data -----//
function getAllLeadsData() {
    $conn = db_connect();
    $sql = "SELECT
                c.client_id,
                c.client_name,
                c.client_rep,
                c.client_location,
                c.client_email,
                c.client_phone_num,
                s.service_id,
                s.service_name,
                pm.prospect_service_remarks,
                r.region_name,
                pm.prospect_date,
                ps.status_id,
                ps.status_name,
                pm.prospect_status_remarks,
                pm.prospect_reason,
                pm.prospect_notice_date,
                pm.prospect_notice_to,
                pm.prospect_month_est,
                pm.prospect_contract_sign,
                pm.prospect_contract_period,
                pm.prospect_contract_start,
                pm.prospect_contract_end,
                pm.prospect_contract_remarks,
                pm.prospect_id,
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
            ORDER BY
                c.client_name ASC";
    $result = $conn->query($sql);
    if (!$result) {
        error_log("Error in query: " . $conn->error);
        $conn->close();
        return false;
    }
    $data = array();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $conn->close();
    return $data;
}
//----- Export Leads Table To CSV Function -----//
function generateCSVData($allLeadsData) {
    if (!$allLeadsData) {
        return false;
    }
    $csvData = array(
        array(
            'No.',
            'Company',
            'Client Representative',
            'City/Municipality',
            'Region',
            'Email',
            'Phone Number',
            'Project',
            'Specific Service',
            'Date',
            'Status',
            'Remarks',
            'Reason (If Not Won)',
            'Date of Notice Of Award',
            'Notice to Proceed',
            'Est. Monthly Revenue',
            'Contract',
            'Contract Status',
            'Period',
            'Start Date',
            'End Date',
            'Remarks',
        )
    );
    $row_number = 1;
    foreach ($allLeadsData as $row) {
        $prospect_date = ($row['prospect_date'] == '' || $row['prospect_date'] == '0000-00-00') ? 'N/A' : $row['prospect_date'];
        $prospect_notice_date = ($row['prospect_notice_date'] == '' || $row['prospect_notice_date'] == '0000-00-00') ? 'N/A' : $row['prospect_notice_date'];
        $prospect_notice_to = ($row['prospect_notice_to'] == '' || $row['prospect_notice_to'] == '0000-00-00') ? 'N/A' : $row['prospect_notice_to'];
        $prospect_contract_sign = ($row['prospect_contract_sign'] == '' || $row['prospect_contract_sign'] == '0000-00-00') ? 'TBA' : $row['prospect_contract_sign'];
        $prospect_contract_start = ($row['prospect_contract_start'] == '' || $row['prospect_contract_start'] == '0000-00-00') ? 'TBD' : $row['prospect_contract_start'];
        $prospect_contract_end = ($row['prospect_contract_end'] == '' || $row['prospect_contract_end'] == '0000-00-00') ? 'TBD' : $row['prospect_contract_end'];
        $csvData[] = array(
            $row_number,
            $row['client_name'],
            $row['client_rep'],
            $row['client_location'],
            $row['region_name'],
            $row['client_email'],
            $row['client_phone_num'],
            $row['service_name'],
            $row['prospect_service_remarks'],
            $prospect_date,
            $row['status_name'],
            $row['prospect_status_remarks'],
            $row['prospect_reason'],
            $prospect_notice_date,
            $prospect_notice_to,
            $row['prospect_month_est'],
            $prospect_contract_sign,
            $row['contract_status_name'],
            $row['prospect_contract_period'],
            $prospect_contract_start,
            $prospect_contract_end,
            $row['prospect_contract_remarks']
        );
        $row_number++;
    }
    return $csvData;
}
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    $allLeadsData = getAllLeadsData();
    if (!$allLeadsData) {
        echo "Error fetching leads data.";
        exit;
    }
    $csvData = generateCSVData($allLeadsData);
    if (!$csvData) {
        echo "Error generating CSV data.";
        exit;
    }
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="SLI_LEADS_MONITORING.csv"');
    $output = fopen('php://output', 'w');
    foreach ($csvData as $row) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}
?>