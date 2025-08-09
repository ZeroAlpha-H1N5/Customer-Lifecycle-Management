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
        var closeButtons = modal.find(".close, .cancel-button");
        if (openButton.length) {
            openButton.click(function() {
                modal.show();
            });
        } else if (openButtonId) {
             console.warn(`Button with ID '${openButtonId}' not found for setupModal.`);
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
    setupModal("#addMeetModal", "#addMeetButton");
// -----        Add/Edit/Delete Respo Function        ------ //
    const newRespoInput = $('#new_respo');
    const respoSelect = $('#respo');
    const editNewRespoInput = $('#edit_new_respo');
    const editRespoSelect = $('#edit_respo');
    $('#add_respo_button').on('click', function(event) {
        event.preventDefault();
        addResponsibleParty(newRespoInput, respoSelect);
    });
    $('#delete_respo_button').on('click', function(event) {
        event.preventDefault();
        deleteResponsibleParty(respoSelect);
    });
    $('#edit_add_respo_button').on('click', function(event) {
        event.preventDefault();
        addResponsibleParty(editNewRespoInput, editRespoSelect);
    });
    $('#edit_delete_respo_button').on('click', function(event) {
        event.preventDefault();
        deleteResponsibleParty(editRespoSelect);
    });
    $('#new_respo, #edit_new_respo').keypress(function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            if ($(this).is('#new_respo')) {
                addResponsibleParty(newRespoInput, respoSelect);
            } else {
                addResponsibleParty(editNewRespoInput, editRespoSelect);
            }
        }
    });
    function deleteResponsibleParty(respoSelect) {
        if (respoSelect && respoSelect.length) {
            respoSelect.find(':selected').each(function() {
                $(this).remove();
            });
        } else {
            console.error("Error: respoSelect element not found or invalid in deleteResponsibleParty function.");
        }
    }
    function addResponsibleParty(respoInput, respoSelect) {
        if (respoInput && respoInput.length) {
            const newRespoName = respoInput.val().trim();
            if (newRespoName !== "") {
                let exists = false;
                respoSelect.find('option').each(function() {
                    if ($(this).text().trim().toLowerCase() === newRespoName.toLowerCase()) {
                        exists = true;
                        return false;
                    }
                });
                if (!exists) {
                    const newOption = $('<option></option>').val(newRespoName).text(newRespoName);
                    respoSelect.append(newOption);
                    newOption.prop('selected', true);
                    respoInput.val("");
                } else {
                    console.warn(`Responsible party '${newRespoName}' already exists.`);
                    respoInput.val("");
                    respoSelect.val(newRespoName);
                }
            }
        } else {
            console.error("Error: respoInput element not found or invalid in addResponsibleParty function.");
        }
    }
// -----        Delete Meeting Function        ------ //
    $('body').on('click', '.deleteMeetingButton', function() {
        const meetId = $(this).data('meet-id');
        Swal.fire({
            title: 'Are you sure?',
            text: "You want to delete this meeting? This action cannot be undone.",
            icon: null,
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'Cancel',
            customClass: swalCustomClasses
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: './delete/delete_meets.php',
                    type: 'POST',
                    data: {
                        id: meetId
                    },
                    dataType: 'text',
                    success: function(response) {
                        if (response.toLowerCase().includes('success')) {
                             Swal.fire({
                                title: 'Meeting deleted successfully.',
                                icon: 'success',
                                customClass: swalCustomClasses
                             }).then(() => {
                                location.reload();
                             });
                        } else {
                             Swal.fire({
                                title: 'Error!',
                                text: response || 'Failed to delete meeting.',
                                icon: null,
                                customClass: swalCustomClasses
                             });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Delete Error:', error);
                        Swal.fire({
                            title: 'AJAX Error!',
                            text: 'An error occurred while deleting: ' + error,
                            icon: 'error',
                            customClass: swalCustomClasses
                        });
                    }
                });
            }
        });
    });
// -----        Add Meet Function        ------ //
    const addMeetForm = $('#addMeetForm');
    addMeetForm.on('submit', function(event) {
        event.preventDefault();
        const formData = $(this).serialize();
        $.ajax({
            url: './create/process_meetings.php',
            type: 'POST',
            data: formData,
            dataType: 'text',
            success: function(response) {
                 if (response.toLowerCase().includes('success')) {
                     Swal.fire({
                        title: 'Meeting added successfully!',
                        icon: 'success',
                        customClass: swalCustomClasses
                     }).then(() => {
                        $("#addMeetModal").hide();
                        window.location.reload();
                     });
                 } else {
                     Swal.fire({
                        title: 'Error!',
                        text: response || "Failed to add meeting.",
                        icon: null,
                        customClass: swalCustomClasses
                     });
                 }
            },
            error: function(xhr, status, error) {
                console.error('Add Error:', error);
                 Swal.fire({
                    title: 'AJAX Error!',
                    text: 'An error occurred while adding: ' + error,
                    icon: null,
                    customClass: swalCustomClasses
                });
            }
        });
    });
// -----        Edit Meet Function        ------ //
    $(document).on('click', '.editMeetingButton', function(event) {
        event.preventDefault();
        const meetId = $(this).data('meet-id');
        openEditModal(meetId);
    });
    $(document).on('click', '#editMeetingModal .cancel-button', function() {
        editMeetingModal.css('display', 'none');
    });
    $(document).on('click', '#editMeetingModal .close', function() {
        editMeetingModal.css('display', 'none');
    });
    $(document).on('click', function(event) {
        if ($(event.target).is('#editMeetingModal')) {
            $('#editMeetingModal').css('display', 'none');
        }
    });
    const editMeetingModal = $('#editMeetingModal');
    function openEditModal(meetId) {
        $.ajax({
            url: './fetch/fetch_meeting_data.php?meet_id=' + meetId,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data && data.meet_id) {
                    $('#meet_id').val(data.meet_id);
                    $('#edit_meet_date').val(data.meet_date);
                    $('#edit_meet_client_name').val(data.client_name);
                    $('#edit_meet_source').val(data.meet_source);
                    $('#edit_meet_issue').val(data.meet_issue);
                    $('#edit_meet_action').val(data.meet_action);
                    $('#edit_meet_timeline').val(data.meet_timeline);
                    $('input[name="meet_status"][value="' + data.meet_status_id + '"]').prop('checked', true);
                    $('#edit_meet_prio').val(data.meet_prio_id);
                    $('#edit_meet_remarks').val(data.meet_remarks);
                    $('#edit_respo').empty();
                    if (data.meet_respo) {
                        var respoParties = data.meet_respo.split(', ');
                        for (var i = 0; i < respoParties.length; i++) {
                            var respo = respoParties[i];
                            $('#edit_respo').append($('<option>', {
                                value: respo,
                                text: respo,
                                selected: true
                            }));
                        }
                    }
                    editMeetingModal.css('display', 'block');
                } else {
                     Swal.fire({
                        title: 'Fetch Error',
                        text: 'Failed to fetch meeting details or received invalid data.',
                        icon: null,
                        customClass: swalCustomClasses
                    });
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Fetch Error:', textStatus, errorThrown);
                console.error('Full Response:', jqXHR.responseText);
                 Swal.fire({
                    title: 'AJAX Error!',
                    text: 'An error occurred while fetching meeting details: ' + errorThrown,
                    icon: null,
                    customClass: swalCustomClasses
                 });
            }
        });
    }
// -----        Update Meet Function        ------ //
    const editMeetingForm = $('#editMeetingForm');
    editMeetingForm.on('submit', function(event) {
        event.preventDefault();
        $.ajax({
            url: './update/update_meeting.php',
            type: 'POST',
            data: editMeetingForm.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response && response.success) {
                     Swal.fire({
                        title: 'Meeting updated successfully!',
                        icon: 'success',
                        customClass: swalCustomClasses
                     }).then(() => {
                        editMeetingModal.css('display', 'none');
                        location.reload();
                     });
                } else {
                     Swal.fire({
                        title: 'Update Error',
                        text: 'Error updating meeting: ' + (response && response.message ? response.message : 'Unknown error'),
                        icon: null,
                        customClass: swalCustomClasses
                     });
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Update Error:', textStatus, errorThrown);
                console.error('Response Text:', jqXHR.responseText);
                 Swal.fire({
                    title: 'AJAX Error!',
                    text: 'An error occurred while updating the meeting. Check the console.',
                    icon: null,
                    customClass: swalCustomClasses
                 });
            }
        });
    });
}); 