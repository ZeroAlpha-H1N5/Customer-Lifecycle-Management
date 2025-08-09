<?php
//----- Database Connection & User Validation -----//
require_once './db/functions.php';
if (!is_logged_in()) {header("Location: login.php");exit;}
$username = $_SESSION['username'];
$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credits</title>
    <link rel="stylesheet" href="css/design.css">
    <link rel="icon" type="image/png" href="icons/sx_logo.png">
    <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
    <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
    <script src="js/functions.js"></script>
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
                <li><a href="view_calendar.php">View Calendar</a></li>
                <li><a href="credits.php" class="active">Credits</a></li>
            <?php endif; ?>
            <?php if (has_permission('Exec')) : ?>
                 <li><a href="home.php">Home</a></li>
                 <li><a href="credits.php" class="active">Credits</a></li>
            <?php endif; ?>
            <li><a href="#" id="logoutLink">Logout</a></li>
        </ul>
    </aside>
    <!-- Main Page -->
    <main class="content">
            <h3><span class="highlighted-title">Credits</span></h3>
        <div class="container">
            <div class="dedication">
                <p><i>This System is dedicated to the<br>
                    Business Development Department<br>
                    of Safexpress Logistics Inc.</i></p>
            </div>
            <hr>
            <div class="developers">
                <p><span class="title">Developed by: <br> UDM Students</span></p><br>
                <div class="developer-list">
                    <div class="developer-item">
                        <div class="image">
                            <img src="icons/pic.jpg" alt="Simon Quinzon">
                        </div>
                        <p>Simon Quinzon<br>IT Intern/Developer</p>
                    </div>
                    <div class="developer-item">
                        <div class="image">
                            <img src="icons/pic.jpg" alt="Irish Grace Blanco">
                        </div>
                        <p>Irish Grace Blanco<br>IT Intern/Designer</p>
                    </div>
                </div>
            </div>
            <div class="supervisors">
                <p><span class="title">Supervised by: <br> Business Development Department</span></p><br>
                <div class="supervisor-list">
                    <div class="supervisor-item">
                        <div class="image">
                            <img src="icons/Alexandra-Joyce-Albina.png" alt="Alexandra Joyce Albine">
                        </div>
                        <p>Alexandra Joyce Albina<br>Department Supervisor</p>
                    </div>
                    <div class="supervisor-item">
                        <div class="image">
                            <img src="icons/Ivanna-Samera.png" alt="Ivanna Samera">
                        </div>
                        <p>Ivanna Samera<br>Supervisor</p>
                    </div>
                </div>
            </div>
            <div class="sponsors">
                <p><span class="title">Sponsors: <br> Safexpress Logistics Inc Execom</span></p><br>
                <div class="sponsor-list">
                    <div class="sponsor-item">
                        <div class="image">
                            <img src="icons/Eden-Satinitigan.png" alt="Eden Satinitigan">
                        </div>
                        <p>Eden Satinitigan<br>President</p>
                    </div>
                    <div class="sponsor-item">
                        <div class="image">
                            <img src="icons/Richard-Cunanan.png" alt="Richard Cunanan">
                        </div>
                        <p>Richard Cunanan<br>CEO</p>
                    </div>
                </div>
            </div>
            <div class="adviser">
                <div class="adviser-item">
                        <p><span class="title">Project Manager / Adviser:</span></p><br>
                    <div class="image">
                        <img src="icons/Clarence-Lucido.png" alt="Clarence Lucido"><br>
                    </div>
                    <p>Clarence Lucido<br>BEQMS Supervisor</p>
                </div>  
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