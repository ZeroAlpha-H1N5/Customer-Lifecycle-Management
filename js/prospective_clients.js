$(document).ready(function() {
// -----        Sweet Alert Custom Classes      ------ //
    const swalCustomClasses = {
        popup: 'swal-custom-popup',
        confirmButton: 'swal-custom-confirm-button',
        cancelButton: 'swal-custom-cancel-button',
        denyButton: 'swal-custom-deny-button'
    };
// -----        Modal ID Open Function        ------ //
    function setupModal(modalId, openButtonId) {
        var modal = $(modalId);
        var openButton = $(openButtonId);
        var closeButtons = $(modalId + " .close, " + modalId + " .cancel-button");
        if (openButton.length) {
            openButton.click(function() {
                modal.show();
            });
        }
        closeButtons.click(function() {
            modal.hide();
        });
        $(window).click(function(event) {
            if ($(event.target).is(modal)) {
                modal.hide();
            }
        });
    }
    setupModal("#addLeadsModal", "#addLeadsButton");
    setupModal("#editLeadsModal", null);
// -----        Contract Date Validation/Calculation        ------ //
    function validateAndCalculateDates(contractDateId, startDateId, endDateId, periodId) {
        var contractDate = $(contractDateId).val();
        var startDateVal = $(startDateId).val();
        var endDateVal = $(endDateId).val();
        if (contractDate && startDateVal && endDateVal) {
            let startDate = new Date(startDateVal);
            let endDate = new Date(endDateVal);
            if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
                 Swal.fire({
                    title: 'Invalid Date',
                    text: 'Please ensure Start Date and End Date are valid dates.',
                    icon: null,
                    customClass: swalCustomClasses
                 });
                 return false;
            }
            if (startDate >= endDate) {
                Swal.fire({
                   title: 'Validation Error',
                   text: 'Start Date must be before End Date.',
                   icon: null,
                   customClass: swalCustomClasses
                });
                return false;
            }
        }
        calculateAndDisplayPeriod(startDateId, endDateId, periodId);
        return true;
    }
    function calculateAndDisplayPeriod(contractStartId, contractEndId, periodId) {
        var startDateVal = $(contractStartId).val();
        var endDateVal = $(contractEndId).val();

        if (startDateVal && endDateVal) {
           let startDate = new Date(startDateVal);
           let endDate = new Date(endDateVal);

           if (!isNaN(startDate.getTime()) && !isNaN(endDate.getTime())) {
                if (startDate < endDate) {
                    var timeDiff = endDate.getTime() - startDate.getTime();
                    var daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
                    let periodString = '';
                    if (daysDiff < 30) { periodString = daysDiff + ' days'; }
                    else if (daysDiff < 365) { let months = Math.floor(daysDiff / 30); periodString = months + (months === 1 ? ' month' : ' months'); }
                    else { let years = (daysDiff / 365).toFixed(1); periodString = years + (parseFloat(years) === 1.0 ? ' year' : ' years'); }
                    $(periodId).val(periodString);
                } else { $(periodId).val(''); }
           } else { $(periodId).val(''); }
        } else { $(periodId).val(''); }
    }
    $('#prospect_contract_start, #prospect_contract_end').change(function() {
        calculateAndDisplayPeriod('#prospect_contract_start', '#prospect_contract_end', '#prospect_contract_period');
    });
    $('#prospect_month_est, #client_phone_num').on('input', function() {
        this.value = this.value.replace(/[^0-9\.]/g, '');
    });
// -----        Mark Proposal Function        ------ //
    $('.createProposalButton').click(function() {
        var prospectId = $(this).data('prospect-id');
        $.ajax({
            url: './update/validate_proposal.php',
            method: 'POST',
            data: { prospect_id: prospectId },
            success: function(response) {
                if (response === 'exists') {
                    Swal.fire({
                        title: 'Proposal Exists',
                        text: 'A proposal already exists for this client.',
                        icon: null,
                        customClass: swalCustomClasses
                    });
                } else {
                    Swal.fire({
                        title: 'Create Proposal?',
                        text: "Are you sure you want to mark this client for a proposal?",
                        icon: null,
                        showCancelButton: true,
                        confirmButtonText: 'Yes',
                        cancelButtonText: 'Cancel',
                        customClass: swalCustomClasses
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: './create/create_proposal.php',
                                method: 'POST',
                                data: { prospect_id: prospectId },
                                success: function(createResponse) {
                                    if (createResponse === 'success') {
                                        Swal.fire({
                                            title: 'Proposal successfully created.',
                                            icon: 'success',
                                            customClass: swalCustomClasses
                                        }).then(() => {
                                            window.location.reload();
                                        });
                                    } else {
                                        Swal.fire({
                                            title: 'Error!',
                                            text: createResponse,
                                            icon: 'error',
                                            customClass: swalCustomClasses
                                        });
                                    }
                                },
                                error: function(xhr, status, error) {
                                    Swal.fire({
                                        title: 'AJAX Error!',
                                        text: 'An error occurred while creating the proposal: ' + error,
                                        icon: 'error',
                                        customClass: swalCustomClasses
                                    });
                                }
                            });
                        }
                    });
                }
            },
            error: function(xhr, status, error) {
                 Swal.fire({
                    title: 'AJAX Error!',
                    text: 'An error occurred while checking for existing proposals: ' + error,
                    icon: 'error',
                    customClass: swalCustomClasses
                });
            }
        });
    });
// -----        Add Leads Function        ------ //
    $("#addLeadsForm").submit(function(event) {
        event.preventDefault();
        if (!validateAndCalculateDates(
                '#prospect_contract_sign',
                '#prospect_contract_start',
                '#prospect_contract_end',
                '#prospect_contract_period'
            )) {
            return;
        }
        var formData = new FormData(this);
        $.ajax({
            type: "POST",
            url: "./create/process_leads.php",
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.trim() == 'success') {
                    Swal.fire({
                        title: 'Lead added successfully!',
                        icon: 'success',
                        customClass: swalCustomClasses
                    }).then(() => {
                        $("#addLeadsModal").hide();
                        window.location.reload();
                    });
                } else {
                     Swal.fire({
                        title: 'Error!',
                        text: 'Failed to add lead. Server response: ' + response,
                        icon: null,
                        customClass: swalCustomClasses
                    });
                }
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    title: 'AJAX Error!',
                    text: 'An error occurred while adding the lead: ' + error,
                    icon: null,
                    customClass: swalCustomClasses
                });
                console.error("Error: Please check the PHP. Status: ", status, " Error: ", error, " Response: ", xhr.responseText);
            }
        });
    });
// -----        Delete Leads Function        ------ //
    $(document).on('click', '.deleteButton', function() {
        var prospectId = parseInt($(this).data('prospect-id'), 10);
        var deleteButton = $(this);
        Swal.fire({
            title: 'Are you sure?',
            text: "You want to delete this lead? This action cannot be undone.",
            icon: null,
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'Cancel',
            customClass: swalCustomClasses
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: "./delete/delete_leads.php",
                    data: {
                        prospect_id: prospectId
                    },
                    success: function(response) {
                        if (response.trim() === 'success') {
                            Swal.fire({
                                title: 'Lead Deleted Successfully.',
                                icon: 'success', 
                                customClass: swalCustomClasses
                            }).then(() => {
                                deleteButton.closest("tr").fadeOut(500, function() {
                                    window.location.reload();
                                });
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: 'Failed to delete lead. Server response: ' + response,
                                icon: 'error', 
                                customClass: swalCustomClasses
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({ 
                            title: 'AJAX Error!',
                            text: 'An error occurred while trying to delete the lead: ' + error,
                            icon: null, 
                            customClass: swalCustomClasses
                        });
                    }
                });
            } 
        });
    });
// -----        Edit Leads Function        ------ //
    $(document).on('click', '.editLeadsButton', function() {
        var prospectId = $(this).data('prospect-id');
        var statusId = $(this).data('status-id');
        $('#editLeadsModal').show();
        fetchProspectData(prospectId, statusId);
    });
    $('#edit_prospect_contract_start, #edit_prospect_contract_end').change(function() {
        calculateAndDisplayPeriod('#edit_prospect_contract_start', '#edit_prospect_contract_end', '#edit_prospect_contract_period');
    });
    function fetchProspectData(prospectId, statusId) {
        $.ajax({
            url: './fetch/fetch_prospect_data.php',
            type: 'GET',
            data: {
                prospect_id: prospectId,
                status_id: statusId
            },
            dataType: 'json',
            success: function(data) {
                if (!data) {
                    console.error("No data received for prospect ID: " + prospectId);
                     Swal.fire({
                        title: 'Error!',
                        text: "No data found for this prospect. Please check the console.",
                        icon: null,
                        customClass: swalCustomClasses
                    });
                    $('#editLeadsModal').hide();
                    return;
                }
                if (data.error) {
                    console.error("Error from server: " + data.error);
                     Swal.fire({
                        title: 'Server Error!',
                        text: data.error,
                        icon: null,
                        customClass: swalCustomClasses
                    });
                    $('#editLeadsModal').hide();
                    return;
                }
                try {
                    $('#edit_prospect_id').val(data.prospect_id || '');
                    $('#edit_client_name').val(data.client_name || '');
                    $('#edit_client_rep').val(data.client_rep || '');
                    $('#edit_client_email').val(data.client_email || '');
                    $('#edit_client_phone_num').val(data.client_phone_num || '');
                    $('input[name="region_id"]').prop('checked', false);
                    var regionId = data.region_id;
                    if (regionId) { $('input[name="region_id"][value="' + regionId + '"]').prop('checked', true); }
                    var serviceSelect = $('#edit_service_id'); serviceSelect.empty();
                    if (data.services && Array.isArray(data.services)) { $.each(data.services, function(i, service) { serviceSelect.append($('<option>', { value: service.service_id, text: service.service_name })); }); serviceSelect.val(data.serviceID || ''); } else { console.error("Services data is missing or invalid."); serviceSelect.append($('<option>', { value: '', text: 'Error loading services' })); }
                    $('#edit_prospect_service_remarks').val(data.prospect_service_remarks || '');
                    $('#edit_client_location').val(data.client_location || '');
                    $('#edit_prospect_date').val(data.prospect_date || '');
                    var statusSelect = $('#edit_status_id'); statusSelect.empty();
                    if(data.statuses && Array.isArray(data.statuses)) { $.each(data.statuses, function(i, status) { statusSelect.append($('<option>', { value: status.status_id, text: status.status_name })); }); statusSelect.val(data.status_id || ''); } else { console.error("Statuses data is missing or invalid."); statusSelect.append($('<option>', { value: '', text: 'Error loading statuses' })); }
                    $('#edit_prospect_status_remarks').val(data.prospect_status_remarks || '');
                    $('#edit_prospect_reason').val(data.prospect_reason || '');
                    $('#edit_prospect_notice_date').val(data.prospect_notice_date || '');
                    $('#edit_prospect_notice_to').val(data.prospect_notice_to || '');
                    $('#edit_prospect_month_est').val(data.prospect_month_est || '');
                    $('#edit_prospect_contract_sign').val(data.prospect_contract_sign || '');
                    $('#edit_contract_status_id').val(data.contract_status_id || '');
                    $('#edit_prospect_contract_start').val(data.prospect_contract_start || '');
                    $('#edit_prospect_contract_end').val(data.prospect_contract_end || '');
                    calculateAndDisplayPeriod('#edit_prospect_contract_start', '#edit_prospect_contract_end', '#edit_prospect_contract_period');
                    $('#edit_prospect_contract_remarks').val(data.prospect_contract_remarks || '');
                } catch (e) {
                    console.error("Error populating form fields: ", e);
                    Swal.fire({
                        title: 'Form Error!',
                        text: "Error populating form fields. Check the console for details.",
                        icon: null,
                        customClass: swalCustomClasses
                    });
                    $('#editLeadsModal').hide();
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error Details:", { Status: status, Error: error, ResponseText: xhr.responseText, ReadyState: xhr.readyState });
                Swal.fire({
                    title: 'AJAX Error!',
                    text: "Error fetching prospect data. Please check the console for technical details.",
                    icon: null,
                    customClass: swalCustomClasses
                });
                 $('#editLeadsModal').hide();
            }
        });
    }
// -----        Update Leads Function        ------ //
    $('#editLeadsForm').submit(function(event) {
        event.preventDefault();
        var startDateVal = $('#edit_prospect_contract_start').val();
        var endDateVal = $('#edit_prospect_contract_end').val();
        if (startDateVal && endDateVal) {
            let startDate = new Date(startDateVal);
            let endDate = new Date(endDateVal);
            if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
                 Swal.fire({
                    title: 'Invalid Date',
                    text: 'Please ensure Start Date and End Date are valid dates.',
                    icon: null,
                    customClass: swalCustomClasses
                 });
                 return;
            }
            if (startDate >= endDate) {
                Swal.fire({
                   title: 'Validation Error',
                   text: 'Start Date must be before End Date.',
                   icon: null,
                   customClass: swalCustomClasses
                });
                return;
            }
        }
        var formData = $(this).serialize();
        $.ajax({
            url: './update/update_leads.php',
            type: 'POST',
            data: formData,
            dataType: 'text',
            success: function(response) {
                if (response.trim() === "success") {
                    Swal.fire({
                        title: 'Prospect updated successfully!',
                        icon: 'success',
                        customClass: swalCustomClasses
                    }).then(() => {
                        $('#editLeadsModal').hide();
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Update Error!',
                        text: "Error updating prospect: " + response,
                        icon: null,
                        customClass: swalCustomClasses
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX error:", status, error, xhr.responseText);
                 Swal.fire({
                    title: 'AJAX Error!',
                    text: "An error occurred while updating the prospect. Check console for details.",
                    icon: null,
                    customClass: swalCustomClasses
                });
            }
        });

    });
});