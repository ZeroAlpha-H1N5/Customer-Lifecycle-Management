<?php
//----- Database Connection & Content Type Declaration -----//
header('Content-Type: application/json');
require_once '../db/functions.php';
$conn = db_connect();
if (!$conn) {
    http_response_code(500);
    echo json_encode(array("error" => "Database connection failed: " . mysqli_connect_error()));
    exit;
}
//----- Fetch Contracts Data & Generate Calendar Display -----//
function getAllCalendarEvents($conn, $filter = 'all') {
    $events = array();
    if ($filter === 'all' || $filter === 'contract') {
        try {
            $sql_contracts = "SELECT
                                prospect_id,
                                client_name,
                                prospect_notice_date,
                                prospect_contract_start,
                                prospect_contract_end
                            FROM prospect_monitor pm
                            JOIN clients c ON pm.client_id = c.client_id";
            $stmt_contracts = $conn->prepare($sql_contracts);
            if ($stmt_contracts === false) {
                throw new Exception("Error preparing contract calendar statement: " . $conn->error);
            }
            if (!$stmt_contracts->execute()) {
                throw new Exception("Error executing contract calendar statement: " . $conn->error);
            }
            $result_contracts = $stmt_contracts->get_result();
            while ($row = $result_contracts->fetch_assoc()) {
                if (!empty($row['prospect_contract_start']) && $row['prospect_contract_start'] != '0000-00-00') {
                    $events[] = array(
                        'id' => 'contract_' . $row['prospect_id'] . '_start',
                        'title' => $row['client_name'] . ' - Contract Start',
                        'start' => $row['prospect_contract_start'],
                        'allDay' => true,
                        'className' => 'contract-start',
                        'extendedProps' => array(
                            'type' => 'Contract Start',
                            'source' => 'contract',
                            'contract_start' => $row['prospect_contract_start'],
                            'contract_end' => $row['prospect_contract_end']
                        ),
                        'url' => '#',
                        'display' => 'block',
                        'overlap' => true 
                    );
                }
                if (!empty($row['prospect_contract_end']) && $row['prospect_contract_end'] != '0000-00-00') {
                    $events[] = array(
                        'id' => 'contract_' . $row['prospect_id'] . '_end',
                        'title' => $row['client_name'] . ' - Contract End',
                        'start' => $row['prospect_contract_end'],
                        'allDay' => true,
                        'className' => 'contract-end',
                        'extendedProps' => array(
                            'type' => 'Contract End',
                            'source' => 'contract',
                            'contract_start' => $row['prospect_contract_start'],
                            'contract_end' => $row['prospect_contract_end']
                        ),
                        'url' => '#',
                        'display' => 'block',
                        'overlap' => true
                    );
                }
            }
            $stmt_contracts->close();
        } catch (Exception $e) {
            error_log($e->getMessage());
            return array("error" => $e->getMessage());
        }
    }
//----- Fetch Fairs Data & Generate Calendar Display -----//
function addOneDay($date) {
    $date = new DateTime($date);
    $date->modify('+1 day');
    return $date->format('Y-m-d');
}
    if ($filter === 'all' || $filter === 'fair') {
        try {
            $sql_fairs = "SELECT fair_id, fair_title, fair_date_start, fair_date_end, fair_venue, fair_desc FROM trade_fairs";
            $stmt_fairs = $conn->prepare($sql_fairs);
            if ($stmt_fairs === false) {
                throw new Exception("Error preparing fairs calendar statement: " . $conn->error);
            }
            if (!$stmt_fairs->execute()) {
                throw new Exception("Error executing fairs calendar statement: " . $conn->error);
            }
            $result_fairs = $stmt_fairs->get_result();
            while ($row = $result_fairs->fetch_assoc()) {
                if (!empty($row['fair_date_start']) && !empty($row['fair_date_end']) && $row['fair_date_start'] != '0000-00-00' && $row['fair_date_end'] != '0000-00-00') {
                    $fair_date_end_inclusive = addOneDay($row['fair_date_end']);
                    $events[] = array(
                        'id' => 'fair_' . $row['fair_id'],
                        'title' => $row['fair_title'],
                        'start' => $row['fair_date_start'],
                        'end' => $fair_date_end_inclusive,
                        'allDay' => true,
                        'className' => 'fair',
                        'extendedProps' => array(
                            'venue' => $row['fair_venue'],
                            'description' => $row['fair_desc'],
                            'source' => 'fair'
                        ),
                        'url' => '#'
                    );
                }
            }
            $stmt_fairs->close();
        } catch (Exception $e) {
            error_log($e->getMessage());
            return array("error" => $e->getMessage());
        }
    }
    return $events;
}
try {
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
    $events = getAllCalendarEvents($conn, $filter);
    $conn->close();
    if (isset($events['error'])) {
        http_response_code(500);
        echo json_encode(array("error" => $events['error']));
    } else {
        echo json_encode($events);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("error" => $e->getMessage()));
    error_log($e->getMessage());
}
?>