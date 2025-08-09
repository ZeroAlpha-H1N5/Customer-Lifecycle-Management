<?php
//----- Database Connection & Content Type Declaration -----//
require_once '../db/functions.php';
header('Content-Type: application/json');
//----- Delete Fair Function -----//
$response = ['status' => 'error', 'message' => 'Invalid request.'];
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["fair_id"]) && isset($_POST["action"]) && $_POST["action"] == "delete_exhibit") {
    error_log("Received exhibit deletion request. Data: " . print_r($_POST, true));
    $fair_id = sanitize_input($_POST["fair_id"]);
    error_log("Sanitized Fair ID for deletion: " . $fair_id);
    if (!is_numeric($fair_id) || $fair_id <= 0) {
        $response['message'] = 'Invalid exhibit ID provided.';
        error_log("Validation failed: Invalid exhibit ID.");
    } else {
        $conn = db_connect();
        if ($conn === false) {
            $response['message'] = 'Database connection failed.';
            error_log("Database connection failed.");
        } else {
            $sql_delete_exhibit = "DELETE FROM trade_fairs WHERE fair_id = ?";
            $stmt_delete_exhibit = $conn->prepare($sql_delete_exhibit);
            if ($stmt_delete_exhibit === false) {
                $response['message'] = 'Error preparing delete statement: ' . $conn->error;
                error_log("SQL prepare error: " . $conn->error);
            } else {
                $stmt_delete_exhibit->bind_param("i", $fair_id);
                if ($stmt_delete_exhibit->execute()) {
                    if ($stmt_delete_exhibit->affected_rows > 0) {
                        $response['status'] = 'success';
                        $response['message'] = 'Exhibit deleted successfully.';
                        error_log("Exhibit deletion successful for fair_id: " . $fair_id);
                    } else {
                        $response['status'] = 'error';
                        $response['message'] = 'Exhibit not found or already deleted.';
                        error_log("Exhibit deletion warning: No rows affected for fair_id: " . $fair_id);
                    }
                } else {
                    $response['message'] = 'Error executing delete statement: ' . $stmt_delete_exhibit->error;
                    error_log("SQL execute error: " . $stmt_delete_exhibit->error);
                }
                $stmt_delete_exhibit->close();
            }
            $conn->close();
        }
    }
} else {
    error_log("Invalid request received for exhibit deletion.");
}
echo json_encode($response);
exit;
?>