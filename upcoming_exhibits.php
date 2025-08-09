<?php
//----- Database Connection & User Validation -----//
require_once './db/functions.php';
if (!is_logged_in()) {header("Location: login.php"); exit;}
$username = $_SESSION['username'];
$role = $_SESSION['role'];
//----- Filter Fields -----//
$available_filter_fields = array(
    'tf.fair_title' => 'Title',
    'tf.fair_date_start' => 'Date Start',
    'tf.fair_date_end' => 'Date End',
    'tf.fair_venue' => 'Venue',
);
$selected_filter_field = isset($_GET['filter_field']) && array_key_exists($_GET['filter_field'], $available_filter_fields) ? $_GET['filter_field'] : null;
$search_term = isset($_GET['search_term']) ? trim($_GET['search_term']) : '';
$sort_order = isset($_GET['sort_order']) && in_array(strtoupper($_GET['sort_order']), ['ASC', 'DESC']) ? strtoupper($_GET['sort_order']) : 'ASC';
$results_per_page = 10;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
//----- Filtered Proposal Table Function -----//
function getListOfExhibitsTableData($filter_field = null, $search_term = '', $sort_order = 'ASC', $results_per_page = 10, $current_page = 1) {
    $conn = db_connect();
    $start_from = ($current_page - 1) * $results_per_page;
    $sql = "SELECT
                tf.fair_id,
                tf.fair_title,
                tf.fair_date_start,
                tf.fair_date_end,
                tf.fair_venue,
                tf.fair_desc,
                tf.fair_remarks
            FROM trade_fairs tf";
    if ($filter_field && $search_term != '') {$sql .= " WHERE " . $filter_field . " LIKE '%" . $conn->real_escape_string($search_term) . "%'";}
    $sql .= " ORDER BY " . ($filter_field ? $filter_field : 'tf.fair_date_start') . " " . $sort_order;
    $sql .= " LIMIT $start_from, $results_per_page";
    $result = $conn->query($sql);
    $data = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {$data[] = $row;}}
    $conn->close();
    return $data;
}
//----- Total Fairs Count -----//
function getTotalExhibitsCount($filter_field = null, $search_term = '') {
    $conn = db_connect();
    $sql = "SELECT COUNT(*) AS total FROM trade_fairs tf";
    if ($filter_field && $search_term != '') {$sql .= " WHERE " . $filter_field . " LIKE '%" . $conn->real_escape_string($search_term) . "%'";}
    $result = $conn->query($sql);
    $total = $result->fetch_assoc()['total'];
    $conn->close();
    return $total;
}
//----- Display Fairs HTML Table Function -----//
function displayListOfExhibitsTable($data, $current_page, $results_per_page) {
  $html = '<table class="exhibit-data-table">';
  $html .= '<thead>';
  $html .= '<tr>';
  $html .= '<th>No.</th>';
  $html .= '<th>Title</th>';
  $html .= '<th>Date Start</th>';
  $html .= '<th>Date End</th>';
  $html .= '<th>Venue</th>';
  $html .= '<th>Exhibit Description</th>';
  $html .= '<th>Remarks</th>';
  $html .= '<th>Actions</th>';
  $html .= '</tr>';
  $html .= '</thead>';
  $html .= '<tbody>';
    if (count($data) > 0) {
        $row_number = (($current_page - 1) * $results_per_page) + 1;
        foreach ($data as $row) {
            $date_start = ($row['fair_date_start'] == '' || $row['fair_date_start'] == '0000-00-00') ? 'N/A' : htmlspecialchars($row['fair_date_start']);
            $date_end = ($row['fair_date_end'] == '' || $row['fair_date_end'] == '0000-00-00') ? 'N/A' : htmlspecialchars($row['fair_date_end']);
            $fair_id = htmlspecialchars($row['fair_id']);
            $html .= '<tr>';
            $html .= '<td>' . $row_number . '</td>';
            $html .= '<td>' . htmlspecialchars($row['fair_title']) . '</td>';
            $html .= '<td>' . $date_start . '</td>';
            $html .= '<td>' . $date_end . '</td>';
            $html .= '<td>' . htmlspecialchars($row['fair_venue']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['fair_desc']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['fair_remarks']) . '</td>';
            $html .= '<td>';
            $html .= '<button class="editButtonFairs" data-fair-id="' . $fair_id . '">Edit</button>';
            $html .= '<button class="deleteButtonFairs" data-fair-id="' . $fair_id . '">Delete</button>';
            $html .= '</td>';
            $html .= '</tr>';
            $row_number++;
        }
    } else {
        $html .= '<tr><td colspan="8">No data found</td></tr>';
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
//----- Declare Fairs Table -----//
$exhibitData = getListOfExhibitsTableData($selected_filter_field, $search_term, $sort_order, $results_per_page, $current_page);
$total_exhibits = getTotalExhibitsCount($selected_filter_field, $search_term,);
$total_pages = ceil($total_exhibits / $results_per_page);
$exhibitTableHtml = displayListOfExhibitsTable($exhibitData, $current_page, $results_per_page);
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
    <title>Trade Fairs and Exhibitions</title>
    <link rel="stylesheet" href="css/design.css">
    <link rel="icon" type="image/png" href="icons/sx_logo.png">
    <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
    <script src= "js/fairs.js"></script>
    <script src= "js/functions.js"></script> 
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
                <li><a href="upcoming_exhibits.php" class="active">Trade Fairs</a></li>
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
    <h3><span class="highlighted-title">List of Upcoming Trade Fairs and Exhibitions</span></h3>
    <!-- Add Fairs Button -->
    <button id="addFairsButton" class="form-buttons">Add Fair/Exhibit</button> <br> <br>
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
    <!-- Fairs Table -->
    <div id="exhibit-table-container">
        <?php echo $exhibitTableHtml; ?>
    </div>
    <!-- Export Fairs to CSV Button -->
    <a href="./export/export_fairs.php?export_csv" class="fairs-export-button">Export to CSV</a>
    <!-- Add Fairs Form Modal -->
    <div id="addFairsModal" class="modal">
        <div class="modal-content">
            <span class="close">×</span>
            <h3>Add New Fair/Exhibit</h3>
                <form id="addFairsForm" method="post">
                    <div>
                        <label for="fair_title">Title:</label><br>
                        <input type="text" id="fair_title" name="fair_title" placeholder="Title"><br><br>
                    </div>
                    <div>
                        <label for="fair_venue">Venue:</label><br>
                        <input type="text" id="fair_venue" name="fair_venue" placeholder="Venue"><br><br>
                    </div>
                    <div>
                        <label for="fair_date_start">Date Start:</label><br>
                        <input type="date" id="fair_date_start" name="fair_date_start"><br><br>
                    </div>
                    <div>
                        <label for="fair_date_end">Date End:</label><br>
                        <input type="date" id="fair_date_end" name="fair_date_end"><br><br>
                    </div>
                    <div>
                        <label for="fair_desc">Exhibit Description:</label><br>
                        <textarea id="fair_desc" name="fair_desc" rows="4" cols="50" placeholder="Type Here"></textarea><br><br>
                    </div>
                    <div>
                        <label for="fair_remarks">Remarks:</label><br>
                        <textarea id="fair_remarks" name="fair_remarks" rows="4" cols="50" placeholder="Type Here"></textarea><br><br>
                    </div>
                    <div class="modal-buttons">
                        <button type="submit">Save Changes</button>
                        <button type="button" class="cancel-button">Cancel</button>
                    </div>
                </form>
        </div>
    </div>
    <!-- Edit Fairs Form Modal -->
    <div id="editFairsModal" class="modal">
        <div class="modal-content">
            <span class="close">×</span>
            <h3>Edit Fair</h3>
                <form id="editFairForm">
                    <input type="hidden" id="fair_id" name="fair_id">
                    <div>
                        <label for="fair_title">Title:</label> <br>
                        <input type="text" id="edit_fair_title" name="fair_title" placeholder="Title"><br><br>
                    </div>
                    <div>
                        <label for="fair_venue">Venue:</label> <br>
                        <input type="text" id="edit_fair_venue" name="fair_venue" placeholder="Venue"><br><br>
                    </div>
                    <div>
                        <label for="fair_date_start">Date Start:</label> <br>
                        <input type="date" id="edit_fair_date_start" name="fair_date_start"><br><br>
                    </div>
                    <div>
                        <label for="fair_date_end">Date End:</label> <br>
                        <input type="date" id="edit_fair_date_end" name="fair_date_end"><br><br>
                    </div>
                    <div>
                        <label for="fair_desc">Exhibit Description:</label> <br>
                        <textarea id="edit_fair_desc" name="fair_desc"></textarea><br><br>
                    </div>
                    <div>
                        <label for="fair_remarks">Remarks:</label> <br>
                        <textarea id="edit_fair_remarks" name="fair_remarks" placeholder="Type Here"></textarea><br><br>
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
            <h3>Confirm Logout</h>
            <p>Are you sure you want to log out?</p>
            <div class="modal-buttons">
                <button id="confirmLogout">Yes, Logout</button>
                <button id="cancelLogout">Cancel</button>
            </div>
        </div>
    </div>
</main>
</body>
</html>