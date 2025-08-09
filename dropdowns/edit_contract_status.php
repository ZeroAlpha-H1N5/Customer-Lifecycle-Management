<?php
//----- Database Connection -----//
require_once './db/functions.php';
$conn = db_connect();
//----- Contract Status Dropdown Edit -----//
$sql = "SELECT contract_status_id, contract_status_name FROM contract_statuses ORDER BY contract_status_name"; // Adjust the order as needed
$result = $conn->query($sql);
$html = '<label for="edit_contract_status_id">Contract Status:</label><br>';
$html .= '<select id="edit_contract_status_id" name="contract_status_id">';
$html .= '<option value="">-- Select a Status --</option>';
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $status_id = $row['contract_status_id'];
        $status_name = htmlspecialchars($row['contract_status_name']);
        $html .= "<option value=\"$status_id\">$status_name</option>";
    }
} else {
    $html .= '<option value="">No statuses found</option>';
}
$html .= '</select><br><br>';
$conn->close();
$contractStatusDropdownEdit = $html;
?>