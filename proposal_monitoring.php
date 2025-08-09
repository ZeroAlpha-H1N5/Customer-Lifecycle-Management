<?php
//----- Database Connection & User Validation -----//
require_once './db/functions.php';
if (!is_logged_in()) {header("Location: login.php"); exit;}
$username = $_SESSION['username'];
$role = $_SESSION['role'];
//----- Filter Fields -----//
$available_filter_fields = array(
    'c.client_name' => 'Company',
    'c.client_rep' => 'Client Representative',
    'c.client_email' => 'Email Address',
    'c.client_location' => 'Location',
    'pm.prop_series_num' => 'Series No.',
    'pm.prop_date_sent' => 'Date Sent',
    'pm.prop_sent_by' => 'Sent By',
    'ps.proposal_status_name' => 'Proposal Status',
    'pm.prop_signed_date' => 'Signed Proposal Date',
);
$selected_filter_field = isset($_GET['filter_field']) && array_key_exists($_GET['filter_field'], $available_filter_fields) ? $_GET['filter_field'] : null;
$search_term = isset($_GET['search_term']) ? trim($_GET['search_term']) : '';
$sort_order = isset($_GET['sort_order']) && in_array(strtoupper($_GET['sort_order']), ['ASC', 'DESC']) ? strtoupper($_GET['sort_order']) : 'ASC';
$results_per_page = 10;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
//----- Filtered Proposal Table Function -----//
function getProposalMonitoringTableData( $filter_field = null, $search_term = '', $sort_order = 'ASC', $results_per_page = 10, $current_page = 1) {
    $conn = db_connect();
    $start_from = ($current_page - 1) * $results_per_page;
    $sql = "SELECT
            pm.prop_id,
            pm.prospect_id,
            c.client_name AS 'Company',
            c.client_location AS 'Location',
            c.client_phone_num AS 'Phone',
            r.region_name AS 'Region',
            s.service_name AS 'Service',
            prom.prospect_service_remarks AS 'ServiceRemarks',
            pm.prop_series_num AS 'SeriesNo.',
            c.client_email AS 'EmailAddress',
            IFNULL(c.client_rep, '') AS 'ClientRepresentative',
            IFNULL(pm.prop_date_sent, '') AS 'DateSent',
            IFNULL(pm.prop_sent_by, '') AS 'SentBy',
            ps.proposal_status_name AS 'ProposalStatus',
            IFNULL(pm.prop_signed_date, '') AS 'SignedProposalDate',
            IFNULL(pm.prop_remarks, '') AS 'Remarks'
        FROM proposal_monitor pm
        INNER JOIN clients c ON pm.client_id = c.client_id
        INNER JOIN services s ON pm.service_id = s.service_id
        INNER JOIN region r ON c.region_id = r.region_id
        INNER JOIN prospect_monitor prom ON pm.prospect_id = prom.prospect_id
        INNER JOIN proposal_statuses ps ON pm.proposal_status_id = ps.proposal_status_id";
    if ($filter_field && $search_term != '') {$sql .= " WHERE " . $filter_field . " LIKE '%" . $conn->real_escape_string($search_term) . "%'";}
    $sql .= " ORDER BY " . ($filter_field ? $filter_field : 'pm.prop_id') . " " . $sort_order;
    $sql .= " LIMIT $start_from, $results_per_page";
    $result = $conn->query($sql);
    $data = [];
    if ($result && $result->num_rows > 0) {while ($row = $result->fetch_assoc()) {$data[] = $row;}}
    $conn->close();
    return $data;
}
//----- Total Proposal Count -----//
function getTotalProposalCount($filter_field = null, $search_term = ''
) {
    $conn = db_connect();
    $sql = "SELECT COUNT(*) AS total
        FROM proposal_monitor pm
        INNER JOIN clients c ON pm.client_id = c.client_id
        INNER JOIN services s ON pm.service_id = s.service_id
        INNER JOIN prospect_monitor prom ON pm.prospect_id = prom.prospect_id
        INNER JOIN proposal_statuses ps ON pm.proposal_status_id = ps.proposal_status_id";
    if ($filter_field && $search_term != '') {$sql .= " WHERE " . $filter_field . " LIKE '%" . $conn->real_escape_string($search_term) . "%'";}
    $result = $conn->query($sql);
    $total = $result->fetch_assoc()['total'];
    $conn->close();
    return $total;
}
//----- Display Proposals HTML Table Function -----//
function displayProposalMonitoringTable($data) {
    $html = '<table class="proposal-data-table">';
    $html .= '<thead>';
    $html .= '<tr>';
    $html .= '<th>No.</th>';
    $html .= '<th>Series No.</th>';
    $html .= '<th>Company</th>';
    $html .= '<th>Client Representative</th>';
    $html .= '<th>Email Address</th>';
    $html .= '<th>Phone Number</th>';
    $html .= '<th>Location</th>';
    $html .= '<th>Region</th>';
    $html .= '<th>Service</th>';
    $html .= '<th>Service Remarks</th>';
    $html .= '<th>Date Sent</th>';
    $html .= '<th>Sent By</th>';
    $html .= '<th>Proposal Status</th>';
    $html .= '<th>Signed Proposal Date</th>';
    $html .= '<th>Remarks</th>';
    $html .= '<th>Actions</th>';
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';
    if (isset($data["error"])) {$html .= '<tr><td colspan="16">' . htmlspecialchars($data["error"]) . '</td></tr>';}
    elseif (count($data) > 0) {
        foreach ($data as $row) {
            $date_sent = ($row['DateSent'] == '' || $row['DateSent'] == '0000-00-00') ? 'N/A' : htmlspecialchars($row['DateSent']);
            $proposal_sign = ($row['SignedProposalDate'] == '' || $row['SignedProposalDate'] == '0000-00-00') ? 'N/A' : htmlspecialchars($row['SignedProposalDate']);
            $proposal_status_name = htmlspecialchars($row["ProposalStatus"]);
            $proposal_status_class = 'proposal-status-' . str_replace(' ', '', $proposal_status_name);
            $html .= '<tr data-prop-id="' . htmlspecialchars($row["prop_id"]) . '">';
            $html .= '<td>' . htmlspecialchars($row["prop_id"]) . '</td>';
            $html .= '<td>' . htmlspecialchars($row["SeriesNo."]) . '</td>';
            $html .= '<td>' . htmlspecialchars($row["Company"]) . '</td>';
            $html .= '<td>' . htmlspecialchars($row["ClientRepresentative"]) . '</td>';
            $html .= '<td>' . htmlspecialchars($row["EmailAddress"]) . '</td>';
            $html .= '<td>' . htmlspecialchars($row["Phone"]) . '</td>';
            $html .= '<td>' . htmlspecialchars($row["Location"]) . '</td>';
            $html .= '<td>' . htmlspecialchars($row["Region"]) . '</td>';
            $html .= '<td>' . htmlspecialchars($row["Service"]) . '</td>';
            $html .= '<td>' . htmlspecialchars($row["ServiceRemarks"]) . '</td>';
            $html .= '<td>' . $date_sent . '</td>';
            $html .= '<td>' . htmlspecialchars($row["SentBy"]) . '</td>';
            $html .= '<td><span class="' . $proposal_status_class . '">' . $proposal_status_name . '</span></td>';
            $html .= '<td>' . $proposal_sign . '</td>';
            $html .= '<td>' . htmlspecialchars($row["Remarks"]) . '</td>';
            $html .= '<td>
                        <button class="editProposalButton" data-prop-id="' . htmlspecialchars($row["prop_id"]) . '">Edit</button> <br>
                        <button class="deleteProposalButton" data-prop-id="' . htmlspecialchars($row["prop_id"]) . '">Delete</button>
                      </td>';
            $html .= '</tr>';
        }
    } else {$html .= '<tr><td colspan="14">No data found</td></tr>';}
    $html .= '</tbody>';
    $html .= '</table>';
    return $html;
}
//----- Display Pagination Buttons Function -----//
function displayPagination($current_page, $total_pages, $base_url) {
    $html = '<div class="pagination">';
    // First Page
    if ($current_page > 1) {$html .= '<a class="pagination-button" href="' . $base_url . '&page=1"><<</a>';}
    else {$html .= '<span class="pagination-button disabled"><<</span>';}
    // Previous Page
    if ($current_page > 1) {$html .= '<a class="pagination-button" href="' . $base_url . '&page=' . ($current_page - 1) . '"><</a>';}
    else {$html .= '<span class="pagination-button disabled"><</span>';}
    // Page Number Display
    $start_page = max(1, $current_page - 2);
    $end_page = min($total_pages, $current_page + 2);
    for ($i = $start_page; $i <= $end_page; $i++) {
        if ($i == $current_page) {$html .= '<span class="pagination-button current">' . $i . '</span>';}
        else {$html .= '<a class="pagination-button" href="' . $base_url . '&page=' . $i . '">' . $i . '</a>';}
    }
    // Next Page
    if ($current_page < $total_pages) {$html .= '<a class="pagination-button" href="' . $base_url . '&page=' . ($current_page + 1) . '">></a>';}
    else {$html .= '<span class="pagination-button disabled">></span>';}
    // Last Page
    if ($current_page < $total_pages) {$html .= '<a class="pagination-button" href="' . $base_url . '&page=' . $total_pages . '">>></a>';}
    else {$html .= '<span class="pagination-button disabled">>></span>';}
    $html .= '</div>';
    return $html;
}
//----- Declare Proposals Table -----//
$proposalData = getProposalMonitoringTableData($selected_filter_field, $search_term, $sort_order, $results_per_page, $current_page);
$total_proposals = getTotalProposalCount($selected_filter_field, $search_term);
$total_pages = ceil($total_proposals / $results_per_page);
$proposalTableHtml = displayProposalMonitoringTable($proposalData);
$base_url = $_SERVER['PHP_SELF'] . '?';
$params = $_GET;
unset($params['page']);
$base_url .= http_build_query($params);
$paginationHtml = displayPagination($current_page, $total_pages, $base_url);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Proposals</title>
    <link rel="stylesheet" href="css/design.css">
    <link rel="icon" type="image/png" href="icons/sx_logo.png">
    <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
    <script src= "js/functions.js"></script>
    <script src= "js/proposals.js"></script>
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
                    <a href="#" id="leadsMonitoringToggle" class="active">Leads Monitoring</a>
                    <ul class="submenu" id="leadsMonitoringSubmenu">
                        <li><a href="prospective_clients.php">Prospective Clients</a></li>
                        <li><a href="proposal_monitoring.php" class="active">Manage Proposals</a></li>
                        <li><a href="contract_monitoring.php">Manage Accounts</a></li>
                    </ul>
                </li>
                <li><a href="upcoming_exhibits.php">Trade Fairs</a></li>
                <li><a href="meeting_minutes.php">Minutes of Meetings</a></li>
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
    <h3><span class="highlighted-title">Manage Proposals</span></h3>
    <h4>For Follow-Up</h4>
    <!-- Filter/Search Fields -->
    <div class="filterContainer">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
            <select name="filter_field" id="filter_field">
                <option value="" disabled selected>Filter</option>
                <option value="">-- No Filter --</option>
                <?php
                foreach ($available_filter_fields as $field => $label) {
                    $selected = ($selected_filter_field == $field) ? 'selected' : '';
                    echo '<option value="' . $field . '" ' . $selected . '>' . $label . '</option>';
                }?>
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
    <!-- Next/Previous Page Button -->
    <div id="pagination-buttons">
        <?php echo $paginationHtml; ?>
    </div>
    <!-- Proposal Table -->
    <div id="proposal-table-container">
        <?php echo $proposalTableHtml; ?>
    </div>
    <!-- Export Proposals to CSV Button -->
    <a href="./export/export_proposals.php?export_proposals" class="proposal-export-button">Export to CSV</a>
    <!-- Edit Proposal Modal Form -->
    <div id="editProposalModal" class="modal">
    <div class="modal-content">
        <span class="close">×</span>
        <h2 id="editProposalTitle">Editing for Proposal ID: </h2>
        <form id="editProposalForm" method="post" action="process_proposals.php">
            <div>
            <label for="prop_date_sent">Date Sent:</label><br>
            <input type="date" id="prop_date_sent" name="prop_date_sent"><br><br>
            </div>
            <div>
            <label for="prop_sent_by">Sent By:</label><br>
            <input type="text" id="prop_sent_by" name="prop_sent_by" placeholder="Name"><br><br>
            </div>
            <div>
            <label for="proposal_status_id">Proposal Status:</label><br>
            <select id="proposal_status_id" name="proposal_status_id"></select><br><br>
            </div>
            <div>
            <label for="prop_signed_date">Signed Proposal Date:</label><br>
            <input type="date" id="prop_signed_date" name="prop_signed_date"><br><br>
            </div>
            <div>
            <label for="prop_remarks">Remarks:</label><br>
            <textarea id="prop_remarks" name="prop_remarks" rows="4" cols="50" placeholder="Type Here" ></textarea><br><br>
            </div>
            <div class="modal-buttons">
                <button type="submit">Save Changes</button>
                <button type="button" class="cancel-button">Cancel</button>
            </div>
            <input type="hidden" id="prop_id_hidden" name="prop_id">
        </form>
        </div>
    </div>
    <!-- Logout Confirmation Popup -->
    <div id="logoutModal" class="modal">
    <div class="modal-content">
        <span class="close">×</span>
        <h2>Confirm Logout</h2>
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