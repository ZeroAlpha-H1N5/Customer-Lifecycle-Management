$(document).ready(function() {
// -----        Contracts/Fairs Calendar Function           ------ //
    var calendarEl = $('#combined-calendar')[0];
    var calendar;
    function initializeCalendar() {
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            events: function(fetchInfo, successCallback, failureCallback) {
                var filterValue = $('#calendarFilter').val();
                $.ajax({
                    url: './fetch/get_calendar_events.php?filter=' + filterValue,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.error) {
                            console.error("Error fetching events:", response.error);
                            failureCallback();
                        } else {
                            successCallback(response);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", status, error);
                        alert('Failed to fetch events.');
                        failureCallback();
                    }
                });
            },
            eventClick: function(info) {
                if (info.event.extendedProps.source === 'contract') {
                    var companyName = info.event.title.replace(' - Contract Start', '').replace(' - Contract End', '');
                    var contractStartDate = info.event.extendedProps.contract_start ? new Date(info.event.extendedProps.contract_start).toLocaleDateString() : 'N/A';
                    var contractEndDate = info.event.extendedProps.contract_end ? new Date(info.event.extendedProps.contract_end).toLocaleDateString() : 'N/A';
                    Swal.fire({
                        title: companyName ,
                        html:   '<br><b>Start Date:</b> ' + contractStartDate +
                                '<br><b>End Date:</b> ' + contractEndDate,
                        icon: null,
                        confirmButtonText: 'OK',
                        customClass: { 
                            title: 'my-swal-title', 
                            htmlContainer: 'my-swal-content',
                            confirmButton: 'my-swal-button'
                        }
                    });
                } else if (info.event.extendedProps.source === 'fair') {
                    Swal.fire({
                        title: info.event.title,
                        html:   '<br><b>Venue:</b> ' + info.event.extendedProps.venue + 
                                '<br><b>Description:</b> ' + info.event.extendedProps.description,
                        icon: null,
                        confirmButtonText: 'OK',
                        customClass: {
                            title: 'my-swal-title',
                            htmlContainer: 'my-swal-content',
                            confirmButton: 'my-swal-button'
                        }
                    });
                }
                info.jsEvent.preventDefault();
            },
            editable: false
        });
        calendar.render();
        $('#filterButton').off('click').on('click', function() {
            calendar.destroy();
            initializeCalendar();
        });
    }
    initializeCalendar();
    $('#filterButton').off('click').on('click', function(event) {
        event.preventDefault();
        calendar.destroy();
        initializeCalendar();
    });
});