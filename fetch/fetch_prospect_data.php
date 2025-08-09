<?php
//----- Database Connection & Content Type Declaration -----//
header('Content-Type: application/json');
require_once '../db/functions.php';
$conn = db_connect();
//----- Fetch Leads Data -----//
$prospectId = isset($_GET['prospect_id']) ? intval($_GET['prospect_id']) : 0;
$statusId = isset($_GET['status_id']) ? intval($_GET['status_id']) : 0;
$data = array();
if ($prospectId > 0 && $statusId > 0) {
    $sql = "SELECT
        pm.prospect_id,
        c.client_name,
        c.client_rep,
        c.client_email,
        c.client_phone_num,
        c.client_location,
        s.service_id,
        s.service_name,
        pm.prospect_service_remarks,
        r.region_id,
        r.region_name,
        pm.prospect_date,
        ps.status_id,
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
        s.service_id AS serviceID,
        cs.contract_status_id,
        cs.contract_status_name
    FROM prospect_monitor pm
    JOIN clients c ON pm.client_id = c.client_id
    JOIN services s ON pm.service_id = s.service_id
    JOIN prospect_statuses ps ON pm.status_id = ps.status_id
    LEFT JOIN region r ON c.region_id = r.region_id
    LEFT JOIN contract_statuses cs ON pm.contract_status_id = cs.contract_status_id
    WHERE pm.prospect_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("get_prospect_data.php: Error preparing prospect data statement: " . $conn->error);
        $data['error'] = "Error preparing prospect data statement.";
    } else {
        $stmt->bind_param("i", $prospectId);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $data = $row;
//-----         Fetch Allowed Stages By Status Function          -----//
                function getAllowedStages($current_status_id, $conn)
                {
                    $sql = "SELECT stage_id FROM prospect_statuses WHERE status_id = ?";
                    $stmt = $conn->prepare($sql);
                    if ($stmt === false) {
                        return [];
                    }
                    $stmt->bind_param("i", $current_status_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if (!$result) {
                        return [];
                    }
                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        $current_stage_id = $row['stage_id'];
                        $sql = "SELECT allowed_from_stages FROM prospect_stages WHERE stage_id = ?";
                        $stmt = $conn->prepare($sql);
                        if ($stmt === false) {
                            error_log("Error preparing getAllowedStages (stage query) statement: " . $conn->error);
                            return [];
                        }
                        $stmt->bind_param("i", $current_stage_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        if (!$result) {
                            error_log("Error in getAllowedStages (stage query): " . $conn->error);
                            return [];
                        }
                        if ($result->num_rows > 0) {
                            $row = $result->fetch_assoc();
                            $allowed_from_stages = $row['allowed_from_stages'];
                            $allowed_stages = explode(',', $row['allowed_from_stages']);
                            return $allowed_stages;
                        } else {
                            error_log("No matching stage found for current_stage_id: " . $current_stage_id);
                            return [];
                        }
                    } else {
                        error_log("No status found for current_status_id: " . $current_status_id);
                        return [];
                    }
                }
                $data['statuses'] = array();
                $allowedStages = getAllowedStages($statusId, $conn);
                $sql2 = "SELECT status_id, status_name, stage_id FROM prospect_statuses";
                $result2 = $conn->query($sql2);
                if ($result2) {
                    while ($row2 = $result2->fetch_assoc()) {
                        $status_id = $row2['status_id'];
                        $status_name = $row2['status_name'];
                        $stage_id = $row2['stage_id'];
                        if (in_array($stage_id, $allowedStages)) {
                            $data['statuses'][] = array(
                                'status_id' => $status_id,
                                'status_name' => $status_name
                            );
                        }
                    }
                    $result2->close();
                }
//-----                 Fetch Services Function                       -----//
                $sqlServices = "SELECT service_id, service_name FROM services";
                $resultServices = $conn->query($sqlServices);
                $services = array();
                while ($rowServices = $resultServices->fetch_assoc()) {
                    $services[] = array(
                        'service_id' => $rowServices['service_id'],
                        'service_name' => $rowServices['service_name']
                    );
                }
                $data['services'] = $services;
//-----                 Fetch Contract Status Function                 -----//
                $sqlContractStatuses = "SELECT contract_status_id, contract_status_name FROM contract_statuses";
                $resultContractStatuses = $conn->query($sqlContractStatuses);
                $contractStatuses = array();
                while ($rowContractStatuses = $resultContractStatuses->fetch_assoc()) {
                    $contractStatuses[] = array(
                        'status_id' => $rowContractStatuses['contract_status_id'],
                        'status_name' => $rowContractStatuses['contract_status_name']
                    );
                }
                $data['contract_statuses'] = $contractStatuses;
            } else {
                $data['error'] = "No prospect found with that ID.";
                error_log("No prospect found with prospectId = " . $prospectId);

            }
        } else {
            $data['error'] = "Error fetching prospect data: " . $stmt->error;
            error_log("Error fetching prospect data: " . $stmt->error);
        }
    }
} else {
    $data['error'] = "Invalid prospect ID or Status ID.";
    error_log("Invalid prospect ID or Status ID");
}
$conn->close();
echo json_encode($data);
exit;
?>