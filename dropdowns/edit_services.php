<?php
//----- Database Connection -----//
require_once './db/functions.php';
$conn = db_connect();
//----- Services Dropdown Edit -----//
$sql = "SELECT service_id, service_name FROM services ORDER BY service_name";
$result = $conn->query($sql);
$html = '<label for="edit_service_id">Project/Service:</label><br>';
$html .= '<select id="edit_service_id" name="service_id">';
$html .= '<option value="">-- Select a Service --</option>';
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $service_id = $row['service_id'];
        $service_name = htmlspecialchars($row['service_name']);
        $html .= "<option value=\"$service_id\">$service_name</option>";
    }
} else {
    $html .= '<option value="">No services found</option>';
}
$html .= '</select><br><br>';
$conn->close();
$servicesDropdownEdit = $html;
?>