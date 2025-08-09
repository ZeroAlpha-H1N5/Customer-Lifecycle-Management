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
    'c.client_location' => 'City/Municipality',
    's.service_name' => 'Project',
    'pm.prospect_service_remarks' => 'Specific Service',
    'ps.status_name' => 'Status',
    'pm.prospect_date' => 'Date',
    'pm.prospect_contract_sign' => 'Contract Sign Date',
    'cs.contract_status_name' => 'Contract Status',
);
$selected_filter_field = isset($_GET['filter_field']) && array_key_exists($_GET['filter_field'], $available_filter_fields) ? $_GET['filter_field'] : null;
$search_term = isset($_GET['search_term']) ? trim($_GET['search_term']) : '';
$sort_order = isset($_GET['sort_order']) && in_array(strtoupper($_GET['sort_order']), ['ASC', 'DESC']) ? strtoupper($_GET['sort_order']) : 'ASC';
$results_per_page = 10;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
//----- Filtered Proposal Table Function -----//
function getFilteredLeads($filter_field = null, $search_term = '', $sort_order = 'ASC', $results_per_page = 10, $current_page = 1) {
    $conn = db_connect();
    $start_from = ($current_page - 1) * $results_per_page;
    $sql = "SELECT
            c.client_id,
            c.client_name,
            c.client_rep,
            c.client_email,
            c.client_phone_num,
            c.client_location,
            s.service_id,
            s.service_name,
            pm.prospect_service_remarks,
            r.region_name,
            pm.prospect_date,
            ps.status_id,
            ps.status_name,
            pm.prospect_status_remarks,
            pm.prospect_reason,
            pm.prospect_notice_date,
            pm.prospect_notice_to,
            pm.prospect_month_est,
            pm.prospect_contract_sign,
            pm.prospect_contract_period,
            pm.prospect_contract_start,
            pm.prospect_contract_end,
            pm.prospect_contract_remarks,
            cs.contract_status_id,
            cs.contract_status_name,
            pm.prospect_id, 
            ps.status_id
        FROM prospect_monitor pm
        JOIN clients c ON pm.client_id = c.client_id
        JOIN services s ON pm.service_id = s.service_id
        JOIN prospect_statuses ps ON pm.status_id = ps.status_id
        LEFT JOIN region r ON c.region_id = r.region_id
        LEFT JOIN contract_statuses cs ON pm.contract_status_id = cs.contract_status_id";
    if ($filter_field && $search_term != '') {$sql .= " WHERE " . $filter_field . " LIKE '%" . $conn->real_escape_string($search_term) . "%'";}
    if ($filter_field) {$sql .= " ORDER BY $filter_field $sort_order";} 
    else {$sql .= " ORDER BY client_name ASC";}
    $sql .= " LIMIT $start_from, $results_per_page";
    $result = $conn->query($sql);
    $conn->close();
    return $result;
}
//----- Total Leads Count -----//
function getTotalLeadsCount($filter_field = null, $search_term = '') {
    $conn = db_connect();
    $sql = "SELECT COUNT(*) AS total FROM prospect_monitor pm
            JOIN clients c ON pm.client_id = c.client_id
            JOIN prospect_statuses ps ON pm.status_id = ps.status_id
            JOIN services s ON pm.service_id = s.service_id";
    if ($filter_field && $search_term != '') {$sql .= " WHERE " . $filter_field . " LIKE '%" . $conn->real_escape_string($search_term) . "%'";}
    $result = $conn->query($sql);
    $total = $result->fetch_assoc()['total'];
    $conn->close();
    return $total;
}
//----- Display Leads HTML Table Function -----//
function displayLeadsMonitoringTable($result) {
    $html = '<table class="leads-data-table">';
    $html .= '<thead>';
    $html .= '<tr>';
    $html .= '<th>No.</th>';
    $html .= '<th>Company</th>';
    $html .= '<th>Client Representative</th>';
    $html .= '<th>Email Address</th>';
    $html .= '<th>Phone Number</th>';
    $html .= '<th>Project</th>';
    $html .= '<th>Specific Service</th>';
    $html .= '<th>City/Municipality</th>';
    $html .= '<th>Region</th>';
    $html .= '<th>Date</th>';
    $html .= '<th>Status</th>';
    $html .= '<th>Remarks</th>';
    $html .= '<th>Reason (If Not Won)</th>';
    $html .= '<th>Date of Notice of Award</th>';
    $html .= '<th>Notice to Proceed</th>';
    $html .= '<th>Est. Monthly Revenue</th>';
    $html .= '<th>Contract Sign Date</th>';
    $html .= '<th>Contract Status</th>';
    $html .= '<th>Contract Period</th>';
    $html .= '<th>Start Date</th>';
    $html .= '<th>End Date</th>';
    $html .= '<th>Remarks</th>';
    $html .= '<th>Actions</th>';
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';
    if (!$result || $result->num_rows === 0) {$html .= '<tr><td colspan="22">No data found or please apply a filter.</td></tr>';} 
    else {
        $row_number = (($GLOBALS['current_page'] - 1) * $GLOBALS['results_per_page']) + 1;
        while ($row = $result->fetch_assoc()) {
            $prospect_date = ($row['prospect_date'] == '' || $row['prospect_date'] == '0000-00-00') ? 'N/A' : htmlspecialchars($row['prospect_date']);
            $prospect_notice_date = ($row['prospect_notice_date'] == '' || $row['prospect_notice_date'] == '0000-00-00') ? 'N/A' : htmlspecialchars($row['prospect_notice_date']);
            $prospect_notice_to = ($row['prospect_notice_to'] == '' || $row['prospect_notice_to'] == '0000-00-00') ? 'N/A' : htmlspecialchars($row['prospect_notice_to']);
            $prospect_contract_sign = ($row['prospect_contract_sign'] == '' || $row['prospect_contract_sign'] == '0000-00-00') ? 'TBA' : htmlspecialchars($row['prospect_contract_sign']);
            $prospect_contract_start = ($row['prospect_contract_start'] == '' || $row['prospect_contract_start'] == '0000-00-00') ? 'TBD' : htmlspecialchars($row['prospect_contract_start']);
            $prospect_contract_end = ($row['prospect_contract_end'] == '' || $row['prospect_contract_end'] == '0000-00-00') ? 'TBD' : htmlspecialchars($row['prospect_contract_end']);
            $prospect_id = $row['prospect_id'];
            $status_id = $row['status_id'];
            $status_name = htmlspecialchars($row['status_name']); 
            $status_class = 'status-' . str_replace(' ', '', $status_name);
            $contract_status_name = htmlspecialchars($row['contract_status_name']); 
            $contract_status_class = 'contract-status-' . str_replace(' ', '', $contract_status_name); 
            $html .= '<tr>';
            $html .= '<td>' . $row_number . '</td>';
            $html .= '<td>' . $row['client_name'] . '</td>';
            $html .= '<td>' . $row['client_rep'] . '</td>';
            $html .= '<td>' . $row['client_email'] . '</td>';
            $html .= '<td>' . $row['client_phone_num'] . '</td>';
            $html .= '<td>' . $row['service_name'] . '</td>';
            $html .= '<td>' . $row['prospect_service_remarks'] . '</td>';
            $html .= '<td>' . $row['client_location'] . '</td>';
            $html .= '<td>' . $row['region_name'] . '</td>';
            $html .= '<td>' . $prospect_date . '</td>';
            $html .= '<td><span class="' . $status_class . '">' . $status_name . '</span></td>';
            $html .= '<td>' . $row['prospect_status_remarks'] . '</td>';
            $html .= '<td>' . $row['prospect_reason'] . '</td>';
            $html .= '<td>' . $prospect_notice_date . '</td>';
            $html .= '<td>' . $prospect_notice_to . '</td>';
            $html .= '<td>' . $row['prospect_month_est'] . '</td>';
            $html .= '<td>' . $prospect_contract_sign . '</td>';
            $html .= '<td><span class="' . $contract_status_class . '">' . $contract_status_name . '</span></td>';
            $html .= '<td>' . $row['prospect_contract_period'] . '</td>';
            $html .= '<td>' . $prospect_contract_start . '</td>';
            $html .= '<td>' . $prospect_contract_end . '</td>';
            $html .= '<td>' . $row['prospect_contract_remarks'] . '</td>';
            $html .= '<td>
                         <button class="editLeadsButton" data-prospect-id="' . $prospect_id . '" data-status-id="' . $status_id . '">Edit</button>
                         <button class="deleteButton" data-prospect-id="' . $prospect_id . '">Delete</button>
                         <button class="createProposalButton" data-prospect-id="' . $prospect_id . '">Mark For Proposal</button>
                       </td>';
            $html .= '</tr>';
            $row_number++;
        }
    }
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
$leadsResult = getFilteredLeads($selected_filter_field, $search_term, $sort_order, $results_per_page, $current_page);
$total_leads = getTotalLeadsCount($selected_filter_field, $search_term);
$total_pages = ceil($total_leads / $results_per_page);
$leadsTableHtml = displayLeadsMonitoringTable($leadsResult);
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
    <title>Prospective Clients</title>
    <link rel="stylesheet" href="css/design.css">
    <link rel="icon" type="image/png" href="icons/sx_logo.png">
    <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
    <script src= "js/functions.js"></script>
    <script src= "js/prospective_clients.js"></script>
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
                        <li><a href="prospective_clients.php" class="active">Prospective Clients</a></li>
                        <li><a href="proposal_monitoring.php">Manage Proposals</a></li>
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
    <h3><span class="highlighted-title">Prospective Clients</span></h3>
    <!-- Add Leads Button -->
    <button id="addLeadsButton" class="form-buttons">Add New Prospect</button> <br><br>
    <!-- Filter/Search Fields -->
    <div class="filterContainer">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
            <select name="filter_field" id="filter_field">
                <option value="" disabled selected>Filter</option>
                <option value="">-- No Filter --</option>
                <?php foreach ($available_filter_fields as $field => $label) {$selected = ($selected_filter_field == $field) ? 'selected' : ''; echo '<option value="' . $field . '" ' . $selected . '>' . $label . '</option>';}?>
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
    <!-- Leads Table -->
        <div id="leads-table-container">
            <?php echo $leadsTableHtml; ?>
        </div>
    <!-- Export Leads to CSV Button -->
        <a href="./export/export_leads.php?export=csv" class="prospect-export-button">Export to CSV</a>
        <div id="addLeadsModal" class="modal">
            <div class="modal-content">
                <span class="close">×</span>
                <h3>Add New Prospect</h3>
                    <form id="addLeadsForm" method="post" action="./create/process_leads.php">
                        <div>
                            <label for="client_name">Company Name:</label><br>
                            <input type="text" id="client_name" name="client_name" placeholder="Name" required><br><br>
                        </div>
                        <div>
                            <label for="client_rep">Client Representative:</label><br>
                            <input type="text" id="client_rep" name="client_rep" placeholder="Representative"> <br><br>
                        </div>
                        <div>
                            <label for="client_email">Email Address:</label><br>
                            <input type="text" id="client_email" name="client_email" placeholder="Email"><br><br>
                        </div>
                        <div>
                            <label for="client_phone_num">Contact Number</label><br>
                            <input type="text" id="client_phone_num" name="client_phone_num" placeholder="Contact Number"><br><br>
                        </div>
                        <div>
                            <!-- Services Dropdown List -->
                            <?php include "./dropdowns/get_services.php"; echo $servicesDropdown; ?>
                        </div>
                        <div>
                            <label for="prospect_service_remarks" id="prospect_service_remarks">Specific Project</label><br>
                            <input type="text" id="prospect_service_remarks" name="prospect_service_remarks" placeholder="Specific Project" ><br><br>
                        </div>
                        <div>
                            <!-- Statuses Dropdown List -->
                            <?php include "./dropdowns/get_status.php"; echo $statusDropdown; ?>
                        </div>
                        <div>
                            <label for="prospect_date">Date:</label><br>
                            <input type="date" id="prospect_date" name="prospect_date"><br><br>
                        </div>
                        <div>
                            <!-- City/Municipality Text Input -->
                            <label for="client_location">City/Municipality:</label><br>
                            <input type="text" id="client_location" name="client_location" placeholder="City/Municipality" ><br><br>
                        </div>
                        <div>
                            <!-- Region Selection -->
                            <label>Region:</label><br>
                            <div class="radio-region">
                                <input type="radio" id="region_luzon" name="region_id" value="1" checked> <label for="region_luzon">Luzon</label>
                                <input type="radio" id="region_visayas" name="region_id" value="2"> <label for="region_visayas">Visayas</label>
                                <input type="radio" id="region_mindanao" name="region_id" value="3"> <label for="region_mindanao">Mindanao</label><br><br>
                            </div>
                        </div>
                        <div>
                            <label for="prospect_status_remarks">Status Remarks:</label><br>
                            <textarea id="prospect_status_remarks" name="prospect_status_remarks" rows="4" cols="50"  placeholder="Type Here" ></textarea><br><br>
                        </div>
                        <div>
                            <label for="prospect_reason">Reason (If not Won):</label><br>
                            <textarea id="prospect_reason" name="prospect_reason" rows="4" cols="50" placeholder="Type Here" ></textarea><br><br>
                        </div>
                        <div>
                            <!-- Radio buttons for Certified/Not Certified -->
                            <label>ISO 9001:2015 Certified:</label><br>
                            <div class="radio-group">
                                <input type="radio" id="certified_yes" name="certified" value="yes" checked>
                                <label for="certified_yes">Yes</label>

                                <input type="radio" id="certified_no" name="certified" value="no">
                                <label for="certified_no">No</label><br><br>
                            </div>
                        </div>
                        <div>
                            <label for="prospect_notice_date">Date of Notice of Award:</label><br>
                            <input type="date" id="prospect_notice_date" name="prospect_notice_date"><br><br>
                        </div>
                        <div>
                            <label for="prospect_notice_to">Notice to Proceed:</label><br>
                            <input type="date" id="prospect_notice_to" name="prospect_notice_to"><br><br>
                        </div>
                        <div>
                            <label for="prospect_month_est">Estimated Monthly Revenue:</label><br>
                            <input type="text" id="prospect_month_est" name="prospect_month_est" pattern="[0-9]+(.[0-9]+)?" title="Enter a Number"/><br><br>
                        </div>
                        <div>
                            <label for="prospect_contract_sign">Contract Signed Date:</label><br>
                            <input type="date" id="prospect_contract_sign" name="prospect_contract_sign"><br><br>
                        </div>
                        <div>
                            <!-- Contract Status Dropdown -->
                            <?php include "./dropdowns/get_contract_status.php"; echo $contractStatusDropdown; ?>
                        </div>
                        <div>
                            <label for="prospect_contract_start">Start Date:</label><br>
                            <input type="date" id="prospect_contract_start" name="prospect_contract_start"><br><br>
                        </div>
                        <div>
                            <label for="prospect_contract_end">End Date:</label><br>
                            <input type="date" id="prospect_contract_end" name="prospect_contract_end"><br><br>
                        </div>
                        <div>
                            <label>Period:</label><br>
                            <input type="text" id="prospect_contract_period" name="prospect_contract_period" placeholder="Pick a Start Date and End Date"  readonly><br><br>
                        </div>
                        <div>
                            <label for="prospect_contract_remarks">Remarks</label><br>
                            <textarea id="prospect_contract_remarks" name="prospect_contract_remarks" rows="4" cols="50" placeholder="Type Here" ></textarea><br><br>
                        </div>

                        <div class="modal-buttons">
                            <button type="submit">Save Changes</button>
                            <button type="button" class="cancel-button">Cancel</button>
                        </div>
                    </form>
            </div>
        </div>
        <!-- Edit Leads Modal Form -->
        <div id="editLeadsModal" class="modal">
            <div class="modal-content">
                    <span class="close">×</span>
                    <h3>Edit Prospect</h3>
                        <form id="editLeadsForm" method="post" action="update_leads.php">
                            <input type="hidden" id="edit_prospect_id" name="prospect_id">
                            <div>    
                                <label for="edit_client_name">Company Name:</label><br>
                                <input type="text" id="edit_client_name" name="client_name" placeholder="Name"  required><br><br>
                            </div>
                            <div>
                                <label for="edit_client_rep">Client Representative:</label><br>
                                <input type="text" id="edit_client_rep" name="client_rep" placeholder="Representative"><br><br>
                            </div>
                            <div>
                                <label for="edit_client_email">Email Address:</label><br>
                                <input type="text" id="edit_client_email" name="client_email" placeholder="Email" ><br><br>
                            </div>
                            <div>
                                <label for="edit_client_phone_num">Contact Number</label><br>
                                <input type="text" id="edit_client_phone_num" name="client_phone_num" placeholder="Contact Number"><br><br>
                            </div>
                            <div>
                                <!-- Services Dropdown List -->
                                <?php include "./dropdowns/edit_services.php"; echo $servicesDropdownEdit; ?>
                            </div>
                            <div>
                                <label for="edit_prospect_service_remarks">Specific Project</label><br>
                                <input type="text" id="edit_prospect_service_remarks" name="prospect_service_remarks" placeholder="Specific Project" ><br><br>
                            </div>
                            <div>
                                <!-- Statuses Dropdown List -->
                                <label for="edit_status_id">Status:</label><br>
                                <select id="edit_status_id" name="status_id"></select> <br><br>
                            </div>
                            <div>
                                <label for="edit_prospect_date">Date:</label><br>
                                <input type="date" id="edit_prospect_date" name="prospect_date"><br><br>
                            </div>
                            <div>
                                <label for="edit_client_location">City/Municipality:</label><br>
                                <input type="text" id="edit_client_location" name="client_location" placeholder="City/Municipality" ><br><br>
                            </div>
                            <div>
                            <!-- Region Selection -->
                            <label>Region:</label><br>
                                <div class="radio-region">
                                <input type="radio" id="region_luzon" name="region_id" value="1" checked> <label for="region_luzon">Luzon</label>
                                <input type="radio" id="region_visayas" name="region_id" value="2"> <label for="region_visayas">Visayas</label>
                                <input type="radio" id="region_mindanao" name="region_id" value="3"> <label for="region_mindanao">Mindanao</label><br><br>
                                </div>
                            </div>
                            <div>
                                <label for="edit_prospect_status_remarks">Status Remarks:</label><br>
                                <textarea id="edit_prospect_status_remarks" name="prospect_status_remarks" rows="4" cols="50" placeholder="Type Here" ></textarea><br><br>
                            </div>
                            <div>
                                <label for="edit_prospect_reason">Reason (If not Won):</label><br>
                                <textarea id="edit_prospect_reason" name="prospect_reason" rows="4" cols="50" placeholder="Type Here" ></textarea><br><br>
                            </div>
                            <div>
                                <label for="edit_prospect_notice_date">Date of Notice of Award:</label><br>
                                <input type="date" id="edit_prospect_notice_date" name="prospect_notice_date"><br><br>
                            </div>
                            <div>
                                <label for="edit_prospect_notice_to">Notice to Proceed:</label><br>
                                <input type="date" id="edit_prospect_notice_to" name="prospect_notice_to"><br><br>
                            </div>
                            <div>
                                <label for="edit_prospect_month_est">Estimated Monthly Revenue:</label><br>
                                <input type="text" id="edit_prospect_month_est" name="prospect_month_est" pattern="[0-9]+(.[0-9]+)?" placeholder="Enter A Number"/><br><br>
                            </div>
                            <div>
                                <label for="edit_prospect_contract_sign">Contract Signed Date:</label><br>
                                <input type="date" id="edit_prospect_contract_sign" name="prospect_contract_sign"><br><br>
                            </div>
                            <div>
                                <!-- Contract Status Dropdown -->
                                <?php include "./dropdowns/edit_contract_status.php"; echo $contractStatusDropdownEdit; ?>
                            </div>
                            <div>
                                <label for="edit_prospect_contract_start">Start Date:</label><br>
                                <input type="date" id="edit_prospect_contract_start" name="prospect_contract_start"><br><br>
                            </div>
                            <div>
                                <label for="edit_prospect_contract_end">End Date:</label><br>
                                <input type="date" id="edit_prospect_contract_end" name="prospect_contract_end"><br><br>
                            </div>
                            <div>
                                <label>Period:</label><br>
                                <input type="text" id="edit_prospect_contract_period" name="prospect_contract_period" placeholder="Pick a Start Date and End Date"  readonly><br><br>
                            </div>
                            <div>
                                <label for="edit_prospect_contract_remarks">Remarks</label><br>
                                <textarea id="edit_prospect_contract_remarks" name="prospect_contract_remarks" rows="4" cols="50" placeholder="Type Here"></textarea><br><br>
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