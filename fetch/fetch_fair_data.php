<?php
//----- Database Connection & Content Type Declaration -----//
header('Content-Type: application/json');
require_once '../db/functions.php';
//----- Fetch Fairs Data -----//
if (!isset($_GET['fair_id']) || !is_numeric($_GET['fair_id'])) {
    echo json_encode(array('error' => 'Invalid fair ID'));
    exit;
}
$fair_id = (int)$_GET['fair_id'];
function getFairDetails($fair_id) {
    $conn = db_connect();
    $sql = "SELECT
                tf.fair_id,
                tf.fair_title,
                tf.fair_date_start,
                tf.fair_date_end,
                tf.fair_venue,
                tf.fair_desc,
                tf.fair_remarks
            FROM
                trade_fairs tf
            WHERE
                tf.fair_id = " . $fair_id;
    $result = $conn->query($sql);
//----- Pass Fairs Data as JSON -----//
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $conn->close();
        return $row;
    } else {
        $conn->close();
        return null;
    }
}
$fair_details = getFairDetails($fair_id);
if ($fair_details) {
    echo json_encode($fair_details);
} else {
    echo json_encode(array('error' => 'Fair not found'));
}
?>