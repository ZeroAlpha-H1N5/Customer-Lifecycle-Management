$(document).ready(function() {
// -----        Leads Menu Toggle Function           ------ //
    $('#leadsMonitoringToggle').click(function(e) {
        e.preventDefault();
        $('#leadsMonitoringSubmenu').slideToggle();
    });
    $('#leadsMonitoringSubmenu').click(function(e) {
        e.stopPropagation();
    });
// -----        Sidebar Collapse Function           ------ //
    $(document).ready(function() {
        $('#sidebarToggle').click(function() {
          $('body').toggleClass('sidebar-collapsed');
          $(this).toggleClass("sidebar-collapsed");
        });
      });
// -----        Logout Modal Function            ------ //
    $('#logoutLink').click(function(e) {
        e.preventDefault();
        $('#logoutModal').show();
    });
    $('#confirmLogout').click(function() {
        window.location.href = "logout.php";
    });
    $('#cancelLogout').click(function() {
        $('#logoutModal').hide();
    });
    $('.close').click(function() {
        $('#logoutModal').hide();
    });
    $(window).click(function(event) {
        if ($(event.target).is('#logoutModal')) {
            $('#logoutModal').hide();
        }
    });
});