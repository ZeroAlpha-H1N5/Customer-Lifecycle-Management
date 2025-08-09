<?php
//----- Database Connection & User Validation -----//
require_once './db/functions.php';
if (!is_logged_in()) {
    header("Location: login.php");
    exit;
}
$username = $_SESSION['username'];
$role = $_SESSION['role'];
//----- Filter Fields -----//
$available_filter_fields = array(
    'c.client_name' => 'Client',
    'mm.meet_source' => 'Source',
    'mm.meet_respo' => 'Responsible',
    'ms.status_name' => 'Status',
    'mp.prio_name' => 'Prioritization',
);
$selected_filter_field = isset($_GET['filter_field']) && array_key_exists($_GET['filter_field'], $available_filter_fields) ? $_GET['filter_field'] : null;
$search_term = isset($_GET['search_term']) ? trim($_GET['search_term']) : '';
$sort_order = isset($_GET['sort_order']) && in_array(strtoupper($_GET['sort_order']), ['ASC', 'DESC']) ? strtoupper($_GET['sort_order']) : 'ASC';
$results_per_page = 10;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
//----- Filtered Meetings Table Function -----//
function getMeetingsTableData(
    $filter_field = null,
    $search_term = '',
    $sort_order = 'ASC',
    $results_per_page = 10,
    $current_page = 1
) {
    $conn = db_connect();
    $start_from = ($current_page - 1) * $results_per_page;

    $sql = "SELECT
                mm.meet_id,
                mm.meet_date AS 'Date',
                c.client_name AS 'Client',
                mm.meet_source AS 'Source',
                mm.meet_issue AS 'Issue',
                mm.meet_action AS 'Action',
                GROUP_CONCAT(mm.meet_respo SEPARATOR ', ') AS 'Respo',
                mm.meet_timeline AS 'Timeline',
                ms.status_name AS meet_status,
                mp.prio_name AS meet_priority,
                mm.meet_remarks
            FROM
                meeting_minutes mm
            JOIN
                clients c ON mm.client_id = c.client_id
            LEFT JOIN
                meet_status ms ON mm.meet_status_id = ms.status_id
            LEFT JOIN
                meet_prio mp ON mm.meet_prio_id = mp.prio_id";
    if ($filter_field && $search_term != '') {
        $sql .= " WHERE " . $filter_field . " LIKE '%" . $conn->real_escape_string($search_term) . "%'";
    }
    $sql .= " GROUP BY mm.meet_id";
    $sql .= " ORDER BY " . ($filter_field ? $filter_field : 'mm.meet_date') . " " . $sort_order;
    $sql .= " LIMIT $start_from, $results_per_page";
    $result = $conn->query($sql);
    $data = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    $conn->close();
    return $data;
}
//----- Total Meetings Count -----//
function getTotalMeetingsCount(
    $filter_field = null,
    $search_term = ''
) {
    $conn = db_connect();
    $sql = "SELECT COUNT(DISTINCT mm.meet_id) AS total
            FROM
                meeting_minutes mm
            JOIN
                clients c ON mm.client_id = c.client_id
            LEFT JOIN
                meet_status ms ON mm.meet_status_id = ms.status_id
            LEFT JOIN
                meet_prio mp ON mm.meet_prio_id = mp.prio_id";

    if ($filter_field && $search_term != '') {
        $sql .= " WHERE " . $filter_field . " LIKE '%" . $conn->real_escape_string($search_term) . "%'";
    }
    $result = $conn->query($sql);
    $total = $result->fetch_assoc()['total'];
    $conn->close();
    return $total;
}
//----- Display Meeting HTML Table Function -----//
function displayMeetingsTable($data, $current_page, $results_per_page) {
    $html = '<table class="meeting-data-table">';
    $html .= '<thead>';
    $html .= '<tr>';
    $html .= '<th>Item No.</th>';
    $html .= '<th>Date</th>';
    $html .= '<th>Client</th>';
    $html .= '<th>Source</th>';
    $html .= '<th>Issue/s</th>';
    $html .= '<th>Action Plan</th>';
    $html .= '<th>Responsible</th>';
    $html .= '<th>Timeline</th>';
    $html .= '<th>Status</th>';
    $html .= '<th>Prioritization</th>';
    $html .= '<th>Remarks</th>';
    $html .= '<th>Actions</th>';
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';
    if (count($data) > 0) {
        $item_no = (($current_page - 1) * $results_per_page) + 1;
        foreach ($data as $row) {
            $meet_id = htmlspecialchars($row["meet_id"]);
            $date = ($row["Date"] == '' || $row["Date"] == '0000-00-00') ? 'N/A' : htmlspecialchars($row["Date"]);
            $timeline = ($row["Timeline"] == '' || $row["Timeline"] == '0000-00-00') ? 'N/A' : htmlspecialchars($row["Timeline"]);
            $meet_status_name = htmlspecialchars($row["meet_status"]);
            $meet_status_class = 'meet-status-' . str_replace(' ', '', $meet_status_name);
            $meet_priority_name = htmlspecialchars($row["meet_priority"]);
            $meet_priority_class = 'meet-priority-' . str_replace(' ', '', $meet_priority_name);
            $html .= '<tr>';
            $html .= '<td>' . $item_no . '</td>';
            $html .= '<td>' . $date . '</td>';
            $html .= '<td>' . htmlspecialchars($row["Client"]) . '</td>';
            $html .= '<td>' . htmlspecialchars($row["Source"]) . '</td>';
            $html .= '<td>' . htmlspecialchars($row["Issue"]) . '</td>';
            $html .= '<td>' . htmlspecialchars($row["Action"]) . '</td>';
            $html .= '<td>' . htmlspecialchars($row["Respo"]) . '</td>';
            $html .= '<td>' . $timeline . '</td>';
            $html .= '<td><span class="' . $meet_status_class . '">' . $meet_status_name . '</span></td>'; 
            $html .= '<td><span class="' . $meet_priority_class . '">' . $meet_priority_name . '</span></td>';
            $html .= '<td>' . htmlspecialchars($row["meet_remarks"]) . '</td>';
            $html .= '<td>
                <button class="editMeetingButton" data-meet-id="' . $meet_id . '">Edit</button>
                <button class="deleteMeetingButton" data-meet-id="' . $meet_id . '">Delete</button>
                <a href="./export/generate_meetings_pdf.php?meet_id=' . $meet_id . '" target="_blank" class="export-mom">Export PDF</a>
            </td>';
            $html .= '</tr>';
            $item_no++;
        }
    } else {
        $html .= '<tr><td colspan="12">No meetings found.</td></tr>';
    }
    $html .= '</tbody>';
    $html .= '</table>';
    return $html;
}
//----- Display Pagination Buttons Function -----//
function displayPagination($current_page, $total_pages, $base_url) {
    $html = '<div class="pagination">';
    if ($current_page > 1) {
        $html .= '<a class="pagination-button" href="' . $base_url . '&page=1"><<</a>';
    } else {
        $html .= '<span class="pagination-button disabled"><<</span>';
    }
    if ($current_page > 1) {
        $html .= '<a class="pagination-button" href="' . $base_url . '&page=' . ($current_page - 1) . '"><</a>';
    } else {
        $html .= '<span class="pagination-button disabled"><</span>';
    }
    $start_page = max(1, $current_page - 2);
    $end_page = min($total_pages, $current_page + 2);
    for ($i = $start_page; $i <= $end_page; $i++) {
        if ($i == $current_page) {
            $html .= '<span class="pagination-button current">' . $i . '</span>';
        } else {
            $html .= '<a class="pagination-button" href="' . $base_url . '&page=' . $i . '">' . $i . '</a>';
        }
    }
    if ($current_page < $total_pages) {
        $html .= '<a class="pagination-button" href="' . $base_url . '&page=' . ($current_page + 1) . '">></a>';
    } else {
        $html .= '<span class="pagination-button disabled">></span>';
    }
    if ($current_page < $total_pages) {
        $html .= '<a class="pagination-button" href="' . $base_url . '&page=' . $total_pages . '">>></a>';
    } else {
        $html .= '<span class="pagination-button disabled">>></span>';
    }
    $html .= '</div>';
    return $html;
}
//----- Display Filter/Search Elements -----//
function generateFilterForm($available_filter_fields, $selected_filter_field, $search_term, $sort_order) {
    $html = '<form method="get" class="filter-form">';
    $html .= '<label for="filter_field">Filter By:</label>';
    $html .= '<select name="filter_field" id="filter_field">';
    $html .= '<option value="">All Fields</option>';
    foreach ($available_filter_fields as $field => $label) {
        $selected = ($selected_filter_field == $field) ? 'selected' : '';
        $html .= '<option value="' . htmlspecialchars($field) . '" ' . $selected . '>' . htmlspecialchars($label) . '</option>';
    }
    $html .= '</select>';
    $html .= '<label for="search_term">Search:</label>';
    $html .= '<input type="text" name="search_term" id="search_term" value="' . htmlspecialchars($search_term) . '">';
    $html .= '<label for="sort_order">Sort Order:</label>';
    $html .= '<select name="sort_order" id="sort_order">';
    $html .= '<option value="ASC" ' . ($sort_order == 'ASC' ? 'selected' : '') . '>Ascending</option>';
    $html .= '<option value="DESC" ' . ($sort_order == 'DESC' ? 'selected' : '') . '>Descending</option>';
    $html .= '</select>';
    $html .= '<button type="submit">Apply Filter</button>';
    $html .= '</form>';
    return $html;
}
$meetingsData = getMeetingsTableData(
    $selected_filter_field,
    $search_term,
    $sort_order,
    $results_per_page,
    $current_page
);
$total_meetings = getTotalMeetingsCount(
    $selected_filter_field,
    $search_term,
);
$total_pages = ceil($total_meetings / $results_per_page);
$filterFormHtml = generateFilterForm($available_filter_fields, $selected_filter_field, $search_term, $sort_order);
$meetingsTableHtml = displayMeetingsTable($meetingsData, $current_page, $results_per_page);
$base_url = $_SERVER['PHP_SELF'] . '?';
$params = $_GET;
unset($params['page']);
$base_url .= http_build_query($params);
$paginationHtml = displayPagination($current_page, $total_pages, $base_url);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minutes of Meetings</title>
    <link rel="stylesheet" href="css/design.css">
    <link rel="icon" type="image/png" href="icons/sx_logo.png">
    <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
    <script src= "js/functions.js"></script>
    <script src= "js/meeting_minutes.js"></script>
</head>
<body>
    <!-- Sidebar Button -->
    <a href="#" id="sidebarToggle" class="sidebar-toggle-button">
        <span>☰</span>
    </a>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo-container">
            <a href="home.php" style="text-decoration: none;">
                <img src="icons/sx_logo.png" alt="SafeXpress Logistics Logo" style="cursor: pointer;">
            </a>
        </div>
        <ul>
            <?php if (has_permission('Admin') || has_permission('BD')): ?>
                <li><a href="home.php">Home</a></li>
                <li class="has-submenu">
                    <a href="#" id="leadsMonitoringToggle">Leads Monitoring</a>
                    <ul class="submenu" id="leadsMonitoringSubmenu">
                        <li><a href="prospective_clients.php">Prospective Clients</a></li>
                        <li><a href="proposal_monitoring.php">Manage Proposals</a></li>
                        <li><a href="contract_monitoring.php">Manage Accounts</a></li>
                    </ul>
                </li>
                <li><a href="upcoming_exhibits.php">Trade Fairs</a></li>
                <li><a href="meeting_minutes.php" class="active">Minutes of Meetings</a></li>
                <li><a href="view_calendar.php">View Calendar</a></li>
                <li><a href="credits.php">Credits</a></li>
            <?php endif; ?>
            <?php if (has_permission('Exec')) : ?>
                 <li><a href="home.php">Home</a></li>
                 <li><a href="credits.php">Credits</a></li>
            <?php endif; ?>
            <li><a href="#" id="logoutLink">Logout</a></li>
        </ul>
    </aside>
    <!-- Main Page -->
    <main class="content">
    <h3><span class="highlighted-title">Minutes of Meetings</span></h3>
    <!-- Add Meeting Button -->
    <button id="addMeetButton" class="form-buttons">Add Meet</button> <br><br>
    <!-- Filter/Search Fields -->
    <div class="filterContainer">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
            <select name="filter_field" id="filter_field">
                <option value="" disabled selected>Filter</option>
                <option value="">-- No Filter --</option>
                <?php foreach ($available_filter_fields as $field => $label) {$selected = ($selected_filter_field == $field) ? 'selected' : '';echo '<option value="' . $field . '" ' . $selected . '>' . $label . '</option>';}?>
            </select>
            <input type="text" name="search_term" id="search_term" value="<?php echo htmlspecialchars($search_term); ?>" placeholder="Search">
            <div class="sort-order-group">
                <label for="sort_order_asc"><span class="radio-highlight">
                    <input type="radio" name="sort_order" id="sort_order_asc" value="ASC" <?php echo ($sort_order == 'ASC') ? 'checked' : ''; ?>>
                    Ascending</span>
                </label>
                <label for="sort_order_desc"><span class="radio-highlight">
                    <input type="radio" name="sort_order" id="sort_order_desc" value="DESC" <?php echo ($sort_order == 'DESC') ? 'checked' : ''; ?>>
                    Descending</span>
                </label>
            </div>
            <div class="filter-buttons">
                <button type="submit">Apply Filter</button>
            </div>
        </form>
    </div>
    <!-- Next/Previous Page Buttons -->
    <div id="pagination-buttons">
    <?php echo $paginationHtml; ?>
    </div> 
    <!-- Meetings Table -->
    <div id="meetings-table-container">
        <?php echo $meetingsTableHtml; ?>
    </div>
    <!-- Export To CSV -->
    <a href="./export/export_meetings.php?export_meetings=csv&filter_field=<?php echo htmlspecialchars($_GET['filter_field'] ?? ''); ?>&search_term=<?php echo htmlspecialchars($_GET['search_term'] ?? ''); ?>&sort_order=<?php echo htmlspecialchars($_GET['sort_order'] ?? 'ASC'); ?>" class="meetings-export-button">Export to CSV</a>
    <!-- Add Meet Modal Form -->
    <div id="addMeetModal" class="modal">
        <div class="modal-content">
            <span class="close">×</span>
            <h3>Add Meeting Record</h3>
            <form id="addMeetForm">
                <div>
                    <label for="meet_date">Date:</label><br>
                    <input type="date" id="meet_date" name="meet_date" required><br><br>
                </div>
                <div>
                    <label for="meet_client_name">Client:</label><br>
                    <input type="text" id="meet_client_name" name="meet_client_name" placeholder="Name" required><br><br>
                </div>
                <div>
                    <label for="meet_source">Source:</label><br>
                    <input type="text" id="meet_source" name="meet_source" placeholder="Source" required><br><br>
                </div>
                <div>
                    <label for="meet_issue">Issue/s:</label><br>
                    <textarea id="meet_issue" name="meet_issue" rows="4" cols="50" placeholder="Type Here" ></textarea><br><br>
                </div>
                <div>
                    <label for="meet_action">Action Plan:</label><br>
                    <textarea id="meet_action" name="meet_action" rows="4" cols="50" placeholder="Type Here" ></textarea><br><br>
                </div>
                <div>
                    <label for="respo">Responsible Party:</label><br>
                    <input type="text" id="new_respo" placeholder="Add New Responsible Party"> <br> <br>
                    <button type="button" id="add_respo_button">Add</button>
                    <button type="button" id="delete_respo_button">Delete</button> <br> <br>
                    <select id="respo" name="respo[]" multiple>
                        <option value="" disabled>Select or Add Responsible Parties</option>
                    </select> <br> <br>
                </div>
                <div>
                    <label for="meet_timeline">Timeline:</label><br>
                    <input type="date" id="meet_timeline" name="meet_timeline"><br><br>
                </div>
                <div>
                    <label for="meet_prio">Prioritization:</label><br>
                    <select id="meet_prio" name="meet_prio">
                        <option value="" selected>--Select Priority--</option>
                        <option value="1">High</option>
                        <option value="2">Medium</option>
                        <option value="3">Low</option>
                    </select><br><br>
                </div>
                <div>
                    <label>Status:</label><br>
                    <input type="radio" id="meet_status_open" name="meet_status" value="1" checked>
                    <label for="meet_status_open">Open</label>
                    <input type="radio" id="meet_status_done" name="meet_status" value="2">
                    <label for="meet_status_done">Done</label><br><br>
                </div>
                <div>
                    <label for="meet_remarks">Remarks:</label><br>
                    <textarea id="meet_remarks" name="meet_remarks" rows="4" cols="50" placeholder="Type Here"></textarea><br><br>
                </div>
                <div class="modal-buttons">
                    <button type="submit">Save Changes</button>
                    <button type="button" class="cancel-button">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Edit Meet Modal Form -->
    <div id="editMeetingModal" class="modal">
    <div class="modal-content">
        <span class="close">×</span>
        <h3>Edit Meeting Record</h3>
        <form id="editMeetingForm">
            <input type="hidden" id="meet_id" name="meet_id">
            <div>
                <label for="edit_meet_date">Date:</label><br>
                <input type="date" id="edit_meet_date" name="meet_date" required><br><br>
            </div>
            <div>
                <label for="edit_meet_client_name">Client:</label><br>
                <input type="text" id="edit_meet_client_name" name="client_name" placeholder="Name" required><br><br>
            </div>
            <div>
                <label for="meet_source">Source:</label><br>
                <input type="text" id="edit_meet_source" name="meet_source" placeholder="Source" required><br><br>
            </div>
            <div>
                <label for="meet_issue">Issue/s:</label><br>
                <textarea id="edit_meet_issue" name="meet_issue" rows="4" cols="50" placeholder="Type Here"></textarea><br><br>
            </div>
            <div>
                <label for="meet_action">Action Plan:</label><br>
                <textarea id="edit_meet_action" name="meet_action" rows="4" cols="50" placeholder="Type Here"></textarea><br><br>
            </div>
            <div>
                <label for="edit_respo">Responsible Party:</label><br>
                <input type="text" id="edit_new_respo" placeholder="Add New Responsible Party"> <br> <br>
                <button type="button" id="edit_add_respo_button">Add</button>
                <button type="button" id="edit_delete_respo_button">Delete</button> <br> <br>
                <select id="edit_respo" name="meet_respo[]" multiple>
                    <option value="" disabled>Select or Add Responsible Parties</option>
                </select> <br> <br>
            </div>
            <div>
                <label for="meet_timeline">Timeline:</label><br>
                <input type="date" id="edit_meet_timeline" name="meet_timeline"><br><br>
            </div>
            <div>
                <label for="edit_meet_prio">Prioritization:</label><br>
                <select id="edit_meet_prio" name="meet_prio">
                    <option value="" selected>--Select Priority--</option>
                    <option value="1">High</option>
                    <option value="2">Medium</option>
                    <option value="3">Low</option>
                </select><br><br>
            </div>
            <div>
                <label>Status:</label><br>
                <input type="radio" id="meet_status_open" name="meet_status" value="1" checked>
                <label for="meet_status_open">Open</label>
                <input type="radio" id="meet_status_done" name="meet_status" value="2">
                <label for="meet_status_done">Done</label><br><br>
            </div>
            <div>
                <label for="meet_remarks">Remarks:</label><br>
                <textarea id="edit_meet_remarks" name="meet_remarks" rows="4" cols="50" placeholder="Type Here"></textarea><br><br>
            </div>
            <div class="modal-buttons">
                <button type="submit">Save Changes</button>
                <button type="button" class="cancel-button">Cancel</button>
            </div>
        </form>
    </div>
    </div>
    <!-- Logout Confirmation Popup -->
    <div id="logoutModal" class="modal">
        <div class="modal-content">
            <span class="close">×</span>
            <h3>Confirm Logout</h3>
            <p>Are you sure you want to log out?</p>
            <div class="modal-buttons">
                <button id="confirmLogout">Yes, Logout</button> <br> <br>
                <button id="cancelLogout">Cancel</button>
            </div>
        </div>
    </div>
</main>
</body>
</html>