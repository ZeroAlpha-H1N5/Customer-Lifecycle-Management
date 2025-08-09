<?php
//----- Database Connection -----//
require_once './db/functions.php';
$conn = db_connect();
//----- Select Status By Stage Function -----//
$current_status_id = isset($_GET['status_id']) ? $_GET['status_id'] : 0;
function getAllowedStages($current_status_id, $conn) {
    $sql = "SELECT stage_id FROM prospect_statuses WHERE status_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $current_status_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if (!$result) {
        error_log("Error in getAllowedStages (status query): " . $conn->error);
        return [];
    }
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $current_stage_id = $row['stage_id'];
        $sql = "SELECT allowed_from_stages FROM prospect_stages WHERE stage_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $current_stage_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if (!$result) {
            error_log("Error in getAllowedStages (stage query): " . $conn->error);
            return [];
        }
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return explode(',', $row['allowed_from_stages']);
        } else {
            return [];
        }
    } else {
        return [];
    }
}
//----- Client Status Dropdown Edit -----//
$sql = "SELECT status_id, status_name, stage_id FROM prospect_statuses ORDER BY status_id";
$result = $conn->query($sql);
if (!$result) {
    error_log("Error in main query: " . $conn->error);
    $statusDropdownEdit = '<p>Error fetching statuses from the database.</p>';
} else {
    $allowed_stages = getAllowedStages($current_status_id, $conn);
    $html = '<label for="status_id">Status:</label><br>';
    $html .= '<select id="status_id" name="status_id">';
    $html .= '<option value="">-- Select a Status --</option>';
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $status_id = $row['status_id'];
            $status_name = htmlspecialchars($row['status_name']);
            $stage_id = $row['stage_id'];
            if (in_array($stage_id, $allowed_stages)) {
                $selected = ($status_id == $current_status_id) ? 'selected' : '';
                $html .= "<option value=\"$status_id\" $selected>$status_name</option>";
            }
        }
    } else {
        $html .= '<option value="">No statuses found</option>';
    }
    $html .= '</select><br><br>';
    $statusDropdownEdit = $html;
}
$conn->close();
?>