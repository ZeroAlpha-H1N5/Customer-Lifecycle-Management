<?php
//----- Database Connection & User Validation -----//
require_once './db/functions.php';
if (!is_logged_in()) {header("Location: login.php"); exit;}
$username = $_SESSION['username'];
$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Calendar</title>
    <link rel="stylesheet" href="css/design.css">
    <link rel="icon" type="image/png" href="icons/sx_logo.png">
    <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.css' rel='stylesheet' />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
    <script src= "js/functions.js"></script>
    <script src= "js/get_calendar_events.js"></script>
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
                <li><a href="meeting_minutes.php">Minutes of Meetings</a></li>
                <li><a href="view_calendar.php" class="active">View Calendar</a></li>
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
    <h3><span class="highlighted-title">View Calendar</span></h3> <br>
    <!-- Filter Dropdown -->
    <div class="calendarfilterContainer">
        <label for="calendarFilter"></label>
            <select id="calendarFilter">
                <option value="all">All Events</option>
                <option value="contract">Contract Dates</option>
                <option value="fair">Trade Fairs</option>
            </select>
        <!-- Apply Filter Button -->
            <div class="filter-buttons">
                <button id="filterButton">Apply Filter</button>
            </div>
        </div>
        <!-- Calendar -->
        <div class="calendar-container">
            <div id='combined-calendar'></div>
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