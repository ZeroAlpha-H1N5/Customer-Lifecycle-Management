<?php
//----- Database Connection -----//
require_once '../db/functions.php';
//----- Delete Leads Function -----//
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["prospect_id"])) {
    $prospect_id = sanitize_input($_POST["prospect_id"]);
    if (!is_numeric($prospect_id)) {
        echo "Error: Invalid prospect ID.";
        exit;
    }
    $conn = db_connect();
    $conn->begin_transaction();
    try {
        $sql_get_client = "SELECT client_id FROM prospect_monitor WHERE prospect_id = ?";
        $stmt_get_client = $conn->prepare($sql_get_client);
        if ($stmt_get_client === false) {
            throw new Exception("Error preparing get client statement: " . $conn->error);
        }
        $stmt_get_client->bind_param("i", $prospect_id);
        $stmt_get_client->execute();
        $result_get_client = $stmt_get_client->get_result();
        if ($result_get_client->num_rows !== 1) {
            throw new Exception("Error: Prospect not found or client ID is ambiguous.");
        }
        $row_client = $result_get_client->fetch_assoc();
        $client_id = $row_client['client_id'];
        $stmt_get_client->close();
        $sql_delete_prospect = "DELETE FROM prospect_monitor WHERE prospect_id = ?";
        $stmt_delete_prospect = $conn->prepare($sql_delete_prospect);
        if ($stmt_delete_prospect === false) {
            throw new Exception("Error preparing prospect deletion statement: " . $conn->error);
        }
        $stmt_delete_prospect->bind_param("i", $prospect_id);
        $stmt_delete_prospect->execute();
        $stmt_delete_prospect->close();
        $sql_check_prospects = "SELECT COUNT(*) AS prospect_count FROM prospect_monitor WHERE client_id = ?";
        $stmt_check_prospects = $conn->prepare($sql_check_prospects);
        if ($stmt_check_prospects === false) {
          throw new Exception("Error preparing check prospects statement: " . $conn->error);
        }
        $stmt_check_prospects->bind_param("i", $client_id);
        $stmt_check_prospects->execute();
        $result_check_prospects = $stmt_check_prospects->get_result();
        $row_prospects = $result_check_prospects->fetch_assoc();
        $prospect_count = $row_prospects['prospect_count'];
        $stmt_check_prospects->close();
        if($prospect_count === 0){
          $sql_delete_client = "DELETE FROM clients WHERE client_id = ?";
          $stmt_delete_client = $conn->prepare($sql_delete_client);
          if ($stmt_delete_client === false) {
            throw new Exception("Error preparing client deletion statement: " . $conn->error);
          }
          $stmt_delete_client->bind_param("i", $client_id);
          $stmt_delete_client->execute();
          $stmt_delete_client->close();
        }
        $conn->commit();
        echo "success";
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error deleting lead: " . $e->getMessage();
    }
    $conn->close();
} else {
    echo "Invalid request.";
}
?>