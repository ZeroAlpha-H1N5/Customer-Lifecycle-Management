<?php
//----- Database Connection -----//
require_once '../db/functions.php';
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo "Invalid request method.";
    exit;
}
//----- Delete Proposal Function -----//
if (!isset($_POST["prop_id"]) || !is_numeric($_POST["prop_id"])) {
    echo "Invalid proposal ID.";
    exit;
}
$prop_id = intval($_POST["prop_id"]);
$conn = db_connect();
$sql = "DELETE FROM proposal_monitor WHERE prop_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo "Error preparing statement: " . $conn->error;
    $conn->close();
    exit;
}
$stmt->bind_param("i", $prop_id);
if ($stmt->execute()) {
    echo "success";
} else {
    echo "Error deleting record: " . $stmt->error;
}
$stmt->close();
$conn->close();
?>