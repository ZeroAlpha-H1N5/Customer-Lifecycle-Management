<?php
//----- Database Connection & User Validation -----//
require_once './db/functions.php';
if (!is_logged_in()) {header("Location: login.php"); exit;}
$username = $_SESSION['username'];
$role = $_SESSION['role'];
//----- Fetch Expiring Contracts In 30 Days Data -----//
$results_per_page = 10;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
function getClientOnboardingData(
    $results_per_page = 5,
    $current_page = 1
) {
    $conn = db_connect();
    $start_from = ($current_page - 1) * $results_per_page;
    $sql = "SELECT
        c.client_name,
        pm.prospect_contract_end
            FROM
                prospect_monitor pm
            JOIN
                clients c ON pm.client_id = c.client_id
            JOIN
                services s ON pm.service_id = s.service_id
            JOIN
                prospect_statuses ps ON pm.status_id = ps.status_id
            LEFT JOIN
                region r ON c.region_id = r.region_id
            LEFT JOIN
                contract_statuses cs ON pm.contract_status_id = cs.contract_status_id
            WHERE 
                ps.status_id = ?
        AND 
            ((MONTH(pm.prospect_contract_end) = MONTH(CURDATE()) 
        AND 
            YEAR(pm.prospect_contract_end) = YEAR(CURDATE()))
        OR
            (pm.prospect_contract_end BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)))
        AND 
            pm.prospect_contract_end >= CURDATE()
        ORDER BY 
            pm.prospect_contract_end ASC
        LIMIT $start_from, $results_per_page";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $status_id = 14;
    $stmt->bind_param("i", $status_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    $stmt->close();
    return $data;
}
//----- Fetch Total Expiring Contracts Per Month Data -----//
function getTotalClientOnboardingCount() {
    $conn = db_connect();
    $sql = "SELECT COUNT(*) AS total
        FROM
            prospect_monitor pm
        JOIN
            clients c ON pm.client_id = c.client_id
        JOIN
            services s ON pm.service_id = s.service_id
        JOIN
            prospect_statuses ps ON pm.status_id = ps.status_id
        LEFT JOIN
            region r ON c.region_id = r.region_id
        LEFT JOIN
            contract_statuses cs ON pm.contract_status_id = cs.contract_status_id
        WHERE ps.status_id = ?
        AND MONTH(pm.prospect_contract_end) = MONTH(CURDATE())
        AND YEAR(pm.prospect_contract_end) = YEAR(CURDATE())
        AND pm.prospect_contract_end >= CURDATE()";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $status_id = 14;
    $stmt->bind_param("i", $status_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $total = $result->fetch_assoc()['total'];
    $stmt->close();
    return $total;
}
//----- Display Expiring Contracts Table Function -----//
function displayClientOnboardingTable($data, $current_page, $results_per_page) {
    $html = '<table class="contract-data-table">';
    $html .= '<thead>';
    $html .= '<tr>';
    $html .= '<th>Client Name</th>';
    $html .= '<th>Contract End</th>';
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';
    if (count($data) > 0) {
        $row_number = (($current_page - 1) * $results_per_page) + 1;
        foreach ($data as $row) {
            $prospect_contract_end = ($row['prospect_contract_end'] == '' || $row['prospect_contract_end'] == '0000-00-00') ? 'TBD' : htmlspecialchars($row['prospect_contract_end']);

            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($row['client_name']) . '</td>'; 
            $html .= '<td>' . $prospect_contract_end . '</td>';
            $html .= '</tr>';
            $row_number++;
        }
    } else {
        $html .= '<tr><td colspan="2">No Accounts Found.</td></tr>';
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
// ----- Card for Total Prospects ----- //
$conn = db_connect();
$total_prospect_sql = "SELECT COUNT(*) AS prospect_id FROM prospect_monitor";
$total_prospect_result = $conn->query($total_prospect_sql);
if (!$total_prospect_result) {
    die("Error executing query: " . $conn->error);
}
$row = $total_prospect_result->fetch_assoc();
$total_prospects = $row['prospect_id'];
$total_prospects_formatted = number_format($total_prospects);
// ----- Cards for Status Total -----    //
$sql_statuses = "SELECT status_id, status_name FROM prospect_statuses";
$result_statuses = $conn->query($sql_statuses);
if (!$result_statuses) {
    die("Error executing statuses query: " . $conn->error);
}
$status_counts = array();
while ($row_status = $result_statuses->fetch_assoc()) {
    $status_id = $row_status['status_id'];
    $status_name = $row_status['status_name'];
    $sql_status_count = "SELECT COUNT(*) AS prospect_count FROM prospect_monitor WHERE status_id = $status_id";
    $result_count = $conn->query($sql_status_count);
    if (!$result_count) {
        die("Error executing count query for status " . $status_name . ": " . $conn->error);
    }
    $row_count = $result_count->fetch_assoc();
    $prospect_count = $row_count['prospect_count'];
    $status_counts[$status_id] = array(
        'status_name' => $status_name,
        'prospect_count' => $prospect_count
    );
}
$conn->close();
$clientOnboardingData = getClientOnboardingData(
    $results_per_page,
    $current_page
);
$total_records = getTotalClientOnboardingCount();
$total_pages = ceil($total_records / $results_per_page);
$clientOnboardingTableHtml = displayClientOnboardingTable($clientOnboardingData, $current_page, $results_per_page);
$base_url = $_SERVER['PHP_SELF'] . '?';
$params = $_GET;
unset($params['page']);
$base_url .= http_build_query($params);
$paginationHtml = displayPagination($current_page, $total_pages, $base_url);
// ----- Display Hover on Total Expiring Contracts -----    //
$currentMonthName = date('F');
$dynamicTooltipTitle = "Total number of contracts expiring this " . $currentMonthName;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Welcome - Safexpress Logistics Inc.</title>
    <link rel="stylesheet" href="css/design.css">
    <link rel="icon" type="image/png" href="icons/sx_logo.png">
    <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
    <script src="js/dashboard.js"></script>
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
                <li><a href="home.php" class="active">Home</a></li>
                <li class="has-submenu">
                    <a href="#" id="leadsMonitoringToggle">Leads Monitoring</a>
                    <ul class="submenu" id="leadsMonitoringSubmenu">
                        <li><a href="prospective_clients.php">Prospective Clients</a></li>
                        <li><a href="proposal_monitoring.php">Manage Proposals</a></li>
                        <li><a href="contract_monitoring.php">Manage Accounts</a></li>
                    </ul>
                </li>
                <li><a href="upcoming_exhibits.php">Trade Fairs</a></li>
                <li><a href="meeting_minutes.php">Minutes of Meetings</a></li>
                <li><a href="view_calendar.php">View Calendar</a></li>
                <li><a href="credits.php">Credits</a></li>
            <?php endif; ?>

            <?php if (has_permission('Exec')): ?>
                 <li><a href="home.php" class="active">Home</a></li>
                 <li><a href="credits.php">Credits</a></li>
            <?php endif; ?>

            <li><a href="#" id="logoutLink">Logout</a></li>
        </ul>
</aside>
<!-- Main Page -->
<main class="content">
<!-- Statistics Bar -->
    <div class="statistic-cards-container">
    <!-- Total Prospects -->
    <div class="statistic-card">
        <div class="statistic-icon">
            <img src="icons/Total_prospects.png" alt="Total_prospects">
        </div>
        <div class="statistic-number">
            <?php echo $total_prospects_formatted;?>
        </div>
        <div class="statistic-label">
            Total <br> Clients
        </div>
    </div>
    <!-- Leads Won -->
    <div class="statistic-card">
        <div class="statistic-icon">
            <img src="icons/Won.png" alt="Won">
        </div>
        <div class="statistic-number">
            <?php echo isset($status_counts[11]) ? $status_counts[11]['prospect_count'] : 0; ?>
        </div>
        <div class="statistic-label">
            Closed <br> Won
        </div>
    </div>
    <!-- Leads Lost -->
    <div class="statistic-card">
        <div class="statistic-icon">
            <img src="icons/Lost.png" alt="Lost">
        </div>
        <div class="statistic-number">
            <?php echo isset($status_counts[12]) ? $status_counts[12]['prospect_count'] : 0; ?>
        </div>
        <div class="statistic-label">
            Closed <br> Lost
        </div>
    </div>
    <!-- New Leads -->
    <div class="statistic-card">
        <div class="statistic-icon">
            <img src="icons/NewLeads.png" alt="newleads">
        </div>
        <div class="statistic-number">
            <?php echo isset($status_counts[1]) ? $status_counts[1]['prospect_count'] : 0; ?>
        </div>
        <div class="statistic-label">
            New <br> Leads
        </div>
    </div>
    <!-- Follow Up Required -->
    <div class="statistic-card">
        <div class="statistic-icon">
            <img src="icons/For_follow_up.png" alt="followup">
        </div>
        <div class="statistic-number">
            <?php echo isset($status_counts[7]) ? $status_counts[7]['prospect_count'] : 0; ?>
        </div>
        <div class="statistic-label">
            Follow-Up <br> Required
        </div>
    </div>
    <!-- On Hold -->
    <div class="statistic-card">
        <div class="statistic-icon">
            <img src="icons/Put_on_Hold.png" alt="onhold">
        </div>
        <div class="statistic-number">
            <?php echo isset($status_counts[13]) ? $status_counts[13]['prospect_count'] : 0; ?>
        </div>
        <div class="statistic-label">
            On <br> Hold
        </div>
    </div>
    <!-- Negotiation In Progress -->
    <div class="statistic-card">
        <div class="statistic-icon">
            <img src="icons/Negotiation.png" alt="negotiation">
        </div>
        <div class="statistic-number">
            <?php echo isset($status_counts[10]) ? $status_counts[10]['prospect_count'] : 0; ?>
        </div>
        <div class="statistic-label">
            Negotiation <br> In Progress
        </div>
    </div>
    <!-- Client Onboarding -->
    <div class="statistic-card">
        <div class="statistic-icon">
            <img src="icons/Onboarding.png" alt="onboarding">
        </div>
        <div class="statistic-number">
            <?php echo isset($status_counts[14]) ? $status_counts[14]['prospect_count'] : 0; ?>
        </div>
        <div class="statistic-label">
            Onboarding
        </div>
    </div>
</div>
<!-- Leads Conversion Per Month Graph -->
<div class = "graphs1">
        <div class="lead-conversion">
            <div class="graph-title">
                <h4>Lead Conversion Per Month</h4>
                <div class="yearDropdown">
                    <label for="year-select">Select Year:</label>
                    <select id="year-select"></select>
                </div>
            </div>
                <div class="graph-rectangle">
                    <canvas id="lead-dashboard"></canvas>
                </div>
        </div>
        <div class="prospect-pie-chart">
            <div class="graph-title" >   
                <h4>Total Prospect Per Status</h4> 
            </div>
            <div class="graph-pie">
                <canvas id="pie-dashboard"></canvas>
            </div>
        </div>
</div>
<!-- Leads Growth Per Month Graph -->
<div class = "graphs2">
        <div class="leads-growth-graph">
            <div class="graph-title">
                <h4>Leads Growth Per Month</h4>
                <div class="yearDropdown">
                    <label for="yearSelectLineChart">Select Year:</label>
                    <select id="yearSelectLineChart">
                        <option value="">All Years</option>
                        <option value="2020">2020</option>
                        <option value="2021">2021</option>
                        <option value="2022">2022</option>
                        <option value="2023">2023</option>
                        <option value="2024">2024</option>
                        <option value="2025" selected>2025</option>
                    </select>
                </div>
            </div>
            <div class="graph-rectangle">
            <canvas id="lineChartLeadsGrowth"></canvas>
            </div>
        </div>
<!-- Leads Won/Lost Graph -->
    <div class="bar-contract">
        <div class="leads-won-lost-graph">
            <div class="graph-title" >
                <h4>Leads Won/Lost</h4>
            </div>
            <div class="leadsdropdown">
                <div class="yearDropdown">
                    <label for="monthSelect2">Select Month:</label>
                    <select id="monthSelect2">
                        <option value="">All Months</option>
                        <option value="1">January</option>
                        <option value="2">February</option>
                        <option value="3">March</option>
                        <option value="4">April</option>
                        <option value="5">May</option>
                        <option value="6">June</option>
                        <option value="7">July</option>
                        <option value="8">August</option>
                        <option value="9">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>
                </div>
                <div class="yearDropdown" id="leadswonyear">
                    <label for="yearSelect2">Select Year:</label>
                    <select id="yearSelect2">
                        <option value="">All Years</option>
                        <option value="2020">2020</option>
                        <option value="2021">2021</option>
                        <option value="2022">2022</option>
                        <option value="2023">2023</option>
                        <option value="2024">2024</option>
                        <option value="2025" selected>2025</option>
                    </select>
                </div>
            </div>  
        <div class="graph-square">
            <canvas id="reportsBarGraph" ></canvas>
        </div>
    </div>
<!-- Expiring Contracts Table -->
    <div class="contract-container">
        <div class="graph-title" >
            <h4>Contract Expiring</h4> <span class="contracts-expiring" title="<?php echo htmlspecialchars($dynamicTooltipTitle); ?>"> <?php echo $total_records; ?> </span>
        </div>
            <?php echo $clientOnboardingTableHtml; ?>
            <?php echo $paginationHtml; ?>
        </div>
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