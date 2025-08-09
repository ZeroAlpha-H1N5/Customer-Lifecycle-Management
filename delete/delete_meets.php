<?php
//----- Database Connection & Content Type Declaration -----//
require_once '../db/functions.php';
//----- Delete Meet Function -----//
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['id']) && is_numeric($_POST['id'])) {
        $meet_id = $_POST['id'];
        $conn = db_connect();
        $sql = "DELETE FROM meeting_minutes WHERE meet_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $meet_id);
        if ($stmt->execute() === TRUE) {
            echo "Meeting record deleted successfully.";
        } else {
            echo "Error deleting meeting record: " . $conn->error;
        }
        $stmt->close();
        $conn->close();
    } else {
        echo "Invalid or missing meeting ID.";
    }
} else {
    echo "Invalid request method.  Use POST.";
}
?>