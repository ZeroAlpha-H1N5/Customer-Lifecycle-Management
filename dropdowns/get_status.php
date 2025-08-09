<?php
//----- Database Connection -----//
require_once './db/functions.php';
$conn = db_connect();
//----- Lead Status Dropdown Select -----//
$sql = "SELECT status_id, status_name FROM prospect_statuses ORDER BY status_id";
$result = $conn->query($sql);
$html = '<label for="status_id">Status:</label><br>';
$html .= '<select id="status_id" name="status_id">';
$html .= '<option value="">-- Select a Status --</option>';
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $status_id = $row['status_id'];
        $status_name = htmlspecialchars($row['status_name']);
        $html .= "<option value=\"$status_id\">$status_name</option>";
    }
} else {
    $html .= '<option value="">No statuses found</option>';
}
$html .= '</select><br><br>';
$conn->close();
$statusDropdown = $html;
?>