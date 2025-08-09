<?php
//----- Database Connection & Content Type Declaration -----//
header('Content-Type: application/json');
require_once '../db/functions.php';
//----- Fetch Meeting Data -----//
if (isset($_GET['meet_id']) && is_numeric($_GET['meet_id'])) {
    $meet_id = (int)$_GET['meet_id'];
    $conn = db_connect();
    $sql = "SELECT
                mm.meet_id,
                mm.meet_date,
                mm.client_id,
                c.client_name,
                mm.meet_source,
                mm.meet_issue,
                mm.meet_action,
                mm.meet_timeline,
                mp.prio_name,
                mm.meet_status_id,
                mm.meet_prio_id,
                mm.meet_remarks,
                mm.meet_respo
            FROM meeting_minutes mm
            JOIN clients c ON mm.client_id = c.client_id
            LEFT JOIN meet_prio mp ON mm.meet_prio_id = mp.prio_id
            WHERE mm.meet_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        echo json_encode(array('error' => 'Error preparing statement: ' . $conn->error));
        $conn->close();
        exit;
    }
    $stmt->bind_param("i", $meet_id);
    $stmt->execute();
//----- Pass Meeting Data as JSON -----//
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $data = array(
            'meet_id' => $row['meet_id'],
            'meet_date' => $row['meet_date'],
            'client_id' => $row['client_id'],
            'client_name' => $row['client_name'],
            'meet_source' => $row['meet_source'],
            'meet_issue' => $row['meet_issue'],
            'meet_action' => $row['meet_action'],
            'meet_timeline' => $row['meet_timeline'],
            'prio_name' => $row['prio_name'],
            'meet_status_id' => $row['meet_status_id'],
            'meet_prio_id' => $row['meet_prio_id'],
            'meet_remarks' => $row['meet_remarks'],
            'meet_respo' => $row['meet_respo']
        );
        echo json_encode($data);
    } else {
        echo json_encode(array('error' => 'Meeting not found'));
    }
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(array('error' => 'Invalid request'));
}
?>