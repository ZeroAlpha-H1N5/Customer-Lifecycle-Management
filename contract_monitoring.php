<?php
//----- Database Connection & User Validation -----//
require_once './db/functions.php';
if (!is_logged_in()) {header("Location: login.php"); exit;}
$username = $_SESSION['username'];
$role = $_SESSION['role'];
//----- Filter Fields -----//
$available_filter_fields = array(
    'c.client_name' => 'Client Name',
    'c.client_rep' => 'Client Representative',
    'c.client_email' => 'Email Address',
    's.service_name' => 'Project',
    'pm.prospect_service_remarks' => 'Specific Service',
    'cm.city_municipality_name' => 'City/Municipality',
    'pm.prospect_date' => 'Prospect Date',
    'pm.prospect_contract_sign' => 'Contract Sign Date',
    'cs.contract_status_name' => 'Contract Status',
    'pm.prospect_contract_start' => 'Contract Start',
    'pm.prospect_contract_end' => 'Contract End',
);
$selected_filter_field = isset($_GET['filter_field']) && array_key_exists($_GET['filter_field'], $available_filter_fields) ? $_GET['filter_field'] : null;
$search_term = isset($_GET['search_term']) ? trim($_GET['search_term']) : '';
$sort_order = isset($_GET['sort_order']) && in_array(strtoupper($_GET['sort_order']), ['ASC', 'DESC']) ? strtoupper($_GET['sort_order']) : 'ASC';
$results_per_page = 10;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
//----- Filtered Contracts Table Function -----//
function getClientOnboardingData($filter_field = null, $search_term = '', $sort_order = 'ASC', $results_per_page = 10, $current_page = 1) {
    $conn = db_connect();
    $start_from = ($current_page - 1) * $results_per_page;
    $sql = "SELECT
            c.client_name,
            c.client_rep,
            c.client_email,
            c.client_location,
            c.client_phone_num,
            r.region_name,
            s.service_name,
            pm.prospect_service_remarks,
            pm.prospect_date,
            pm.prospect_notice_date,
            pm.prospect_notice_to,
            pm.prospect_month_est,
            pm.prospect_contract_sign,
            pm.prospect_contract_period,
            pm.prospect_contract_start,
            pm.prospect_contract_end,
            pm.prospect_contract_remarks,
            cs.contract_status_name
        FROM prospect_monitor pm
        JOIN clients c ON pm.client_id = c.client_id
        JOIN services s ON pm.service_id = s.service_id
        JOIN prospect_statuses ps ON pm.status_id = ps.status_id
        LEFT JOIN region r ON c.region_id = r.region_id
        LEFT JOIN contract_statuses cs ON pm.contract_status_id = cs.contract_status_id
        WHERE ps.status_id = ?";
    if ($filter_field && $search_term != '') {$sql .= " AND " . $filter_field . " LIKE '%" . $conn->real_escape_string($search_term) . "%'";}
    $sql .= " ORDER BY " . ($filter_field ? $filter_field : 'c.client_name') . " " . $sort_order;
    $sql .= " LIMIT $start_from, $results_per_page";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {die("Error preparing statement: " . $conn->error);}
    $status_id = 14;
    $stmt->bind_param("i", $status_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    if ($result && $result->num_rows > 0) {while ($row = $result->fetch_assoc()) {$data[] = $row;}}
    $stmt->close();
    $conn->close();
    return $data;
}
//----- Total Contracts Count -----//
function getTotalClientOnboardingCount($filter_field = null, $search_term = '') {
    $conn = db_connect();
    $sql = "SELECT COUNT(*) AS total
        FROM prospect_monitor pm
        JOIN clients c ON pm.client_id = c.client_id
        JOIN services s ON pm.service_id = s.service_id
        JOIN prospect_statuses ps ON pm.status_id = ps.status_id
        LEFT JOIN region r ON c.region_id = r.region_id
        LEFT JOIN contract_statuses cs ON pm.contract_status_id = cs.contract_status_id
        WHERE ps.status_id = ?";
    if ($filter_field && $search_term != '') {$sql .= " AND " . $filter_field . " LIKE '%" . $conn->real_escape_string($search_term) . "%'";}
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {die("Error preparing statement: " . $conn->error);}
    $status_id = 14;
    $stmt->bind_param("i", $status_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $total = $result->fetch_assoc()['total'];
    $stmt->close();
    $conn->close();
    return $total;
}
//----- Display Contracts HTML Table Function -----//
function displayClientOnboardingTable($data, $current_page, $results_per_page) {
  $html = '<table class="contract-data-table">';
  $html .= '<thead>';
  $html .= '<tr>';
  $html .= '<th>Client Name</th>';
  $html .= '<th>Client Representative</th>';
  $html .= '<th>Email Address</th>';
  $html .= '<th>Phone Number</th>';
  $html .= '<th>City/Municipality</th>';
  $html .= '<th>Region</th>';
  $html .= '<th>Project</th>';
  $html .= '<th>Specific Service</th>';
  $html .= '<th>Prospect Date</th>';
  $html .= '<th>Notice Date</th>';
  $html .= '<th>Notice To</th>';
  $html .= '<th>Month Est.</th>';
  $html .= '<th>Contract Sign Date</th>';
  $html .= '<th>Contract Status</th>';
  $html .= '<th>Contract Period</th>';
  $html .= '<th>Contract Start</th>';
  $html .= '<th>Contract End</th>';
  $html .= '<th>Contract Remarks</th>';
  $html .= '</tr>';
  $html .= '</thead>';
  $html .= '<tbody>';
    if (count($data) > 0) {
        $row_number = (($current_page - 1) * $results_per_page) + 1;
        foreach ($data as $row) {
            $prospect_date = ($row['prospect_date'] == '' || $row['prospect_date'] == '0000-00-00') ? 'N/A' : htmlspecialchars($row['prospect_date']);
            $prospect_notice_date = ($row['prospect_notice_date'] == '' || $row['prospect_notice_date'] == '0000-00-00') ? 'N/A' : htmlspecialchars($row['prospect_notice_date']);
            $prospect_notice_to = ($row['prospect_notice_to'] == '' || $row['prospect_notice_to'] == '0000-00-00') ? 'N/A' : htmlspecialchars($row['prospect_notice_to']);
            $prospect_contract_sign = ($row['prospect_contract_sign'] == '' || $row['prospect_contract_sign'] == '0000-00-00') ? 'TBA' : htmlspecialchars($row['prospect_contract_sign']);
            $prospect_contract_start = ($row['prospect_contract_start'] == '' || $row['prospect_contract_start'] == '0000-00-00') ? 'TBD' : htmlspecialchars($row['prospect_contract_start']);
            $prospect_contract_end = ($row['prospect_contract_end'] == '' || $row['prospect_contract_end'] == '0000-00-00') ? 'TBD' : htmlspecialchars($row['prospect_contract_end']);
            $contract_status_name = htmlspecialchars($row['contract_status_name']); 
            $contract_status_class = 'contract-status-' . str_replace(' ', '', $contract_status_name); 
            $html .= '<tr>';
            $html .= '<td>' . ($row['client_name']) . '</td>';
            $html .= '<td>' . ($row['client_rep']) . '</td>';
            $html .= '<td>' . ($row['client_email']) . '</td>';
            $html .= '<td>' . ($row['client_phone_num']) . '</td>';
            $html .= '<td>' . ($row['client_location']) . '</td>';
            $html .= '<td>' . ($row['region_name']) . '</td>';
            $html .= '<td>' . ($row['service_name']) . '</td>';
            $html .= '<td>' . ($row['prospect_service_remarks']) . '</td>';
            $html .= '<td>' . $prospect_date . '</td>';
            $html .= '<td>' . $prospect_notice_date . '</td>';
            $html .= '<td>' . $prospect_notice_to . '</td>';
            $html .= '<td>' . ($row['prospect_month_est']) . '</td>';
            $html .= '<td>' . $prospect_contract_sign . '</td>';
            $html .= '<td><span class="' . $contract_status_class . '">' . $contract_status_name . '</span></td>';
            $html .= '<td>' . ($row['prospect_contract_period']) . '</td>';
            $html .= '<td>' . $prospect_contract_start . '</td>';
            $html .= '<td>' . $prospect_contract_end . '</td>';
            $html .= '<td>' . ($row['prospect_contract_remarks']) . '</td>';
            $html .= '</tr>';
            $row_number++;
        }
    } else {$html .= '<tr><td colspan="18">No Accounts Found.</td></tr>';}
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
    if ($current_page < $total_pages) {
        $html .= '<a class="pagination-button" href="' . $base_url . '&page=' . $total_pages . '">>></a>';
    } else {
        $html .= '<span class="pagination-button disabled">>></span>';}
    $html .= '</div>';
    return $html;
}
//----- Declare Contracts Table -----//
$clientOnboardingData = getClientOnboardingData($selected_filter_field, $search_term, $sort_order, $results_per_page, $current_page);
$total_records = getTotalClientOnboardingCount($selected_filter_field, $search_term);
$total_pages = ceil($total_records / $results_per_page);
$clientOnboardingTableHtml = displayClientOnboardingTable($clientOnboardingData, $current_page, $results_per_page);
$base_url = $_SERVER['PHP_SELF'] . '?';
$params = $_GET;
unset($params['page']);
$base_url .= http_build_query($params);
$paginationHtml = displayPagination($current_page, $total_pages, $base_url);
?>
<!DOCTYPE html>
<html>
<head>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Accounts</title>
    <link rel="stylesheet" href="css/design.css">
    <link rel="icon" type="image/png" href="icons/sx_logo.png">
    <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
    <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
    <script src= "js/functions.js"></script>
</head>
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
                        <li><a href="proposal_monitoring.php">Manage Proposals</a></li>
                        <li><a href="contract_monitoring.php" class="active">Manage Accounts</a></li>
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
    <h3><span class="highlighted-title">View Accounts</span></h3>
    <!-- Force Send Email Button -->
    <form method="post" action="./create/send_email_reminder.php">
        <input type="hidden" name="force_send" value="1">
        <button type="submit" class="form-buttons" name="send_report">Force Send Report</button>
    </form> <br>
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
    <!-- Next/Previous Page Buttons -->
    <div id="pagination-buttons">
        <?php echo $paginationHtml; ?>
    </div>
    <!-- Contracts Table -->
    <div id="contract-table-container">
        <?php echo $clientOnboardingTableHtml; ?>
    </div>
    <!-- Export Contracts to CSV Button -->
    <a href="./export/export_contracts.php" class="form-buttons">Export to CSV</a>
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