$(document).ready(function() {
// -----        Sweet Alert Custom Classes      ------ //
    const swalCustomClasses = {
        popup: 'swal-custom-popup',
        confirmButton: 'swal-custom-confirm-button',
        cancelButton: 'swal-custom-cancel-button',
        denyButton: 'swal-custom-deny-button'
    };
// -----        Proposal Status Dropdown      ------ //
    var proposalStatusData = [
        { proposal_status_id: 1, proposal_status_name: 'Open' },
        { proposal_status_id: 2, proposal_status_name: 'Closed' }
    ];
    function loadProposalStatuses() {
        var select = $('#proposal_status_id');
        select.empty();
        $.each(proposalStatusData, function(index, status) {
            select.append($('<option>', {
                value: status.proposal_status_id,
                text: status.proposal_status_name,
            }));
        });
    }
// -----        Modal Elements      ------ //
    function closeModal() {
        $('#editProposalModal').hide();
    }
    $('.close').on('click', closeModal);
    $('.cancel-button').on('click', closeModal);
// -----        Edit Proposal Function      ------ //
    $(document).on('click', '.editProposalButton', function() {
        var propId = $(this).data('prop-id');
        openEditModal(propId);
        $('#editProposalModal').show();
    });
    function openEditModal(propId) {
        var row = $('tr[data-prop-id="' + propId + '"]');
            if (!row.length) {
                console.error("Could not find row for prop-id:", propId);
                Swal.fire({
                    title: 'Error',
                    text: 'Could not find the proposal data in the table.',
                    icon: 'error',
                    customClass: swalCustomClasses
                });
                return;
            }
        $("#prop_id_hidden").val(propId);
        var email = row.find('td:eq(7)').text().trim();
        var dateSent = row.find('td:eq(8)').text().trim();
        var sentBy = row.find('td:eq(9)').text().trim();
        var proposalStatus = row.find('td:eq(10)').text().trim();
        var signedProposalDate = row.find('td:eq(11)').text().trim();
        var remarks = row.find('td:eq(12)').text().trim();
        $('#editProposalTitle').text('Editing for (Proposal ID: ' + propId + '): ');
        $('#prop_email').val(email);
        $('#prop_date_sent').val(dateSent);
        $('#prop_sent_by').val(sentBy);
        $('#prop_signed_date').val(signedProposalDate);
        $('#prop_remarks').val(remarks);
        loadProposalStatuses();
        var statusId = '';
            if (proposalStatus.toLowerCase() == "closed") {statusId = 2;} 
            else {statusId = 1;}
        $('#proposal_status_id').val(statusId);
    }
// -----        Delete Proposal Function      ------ //
    $('.deleteProposalButton').click(function() {
        var propId = $(this).data('prop-id');
        Swal.fire({
            title: 'Are you sure?',
            text: "You want to delete this proposal? This action cannot be undone.",
            icon: null,
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'Cancel',
            customClass: swalCustomClasses 
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: './delete/delete_proposal.php',
                    method: 'POST',
                    data: { prop_id: propId },
                    success: function(response) {
                        if (response.trim() === 'success') {
                            Swal.fire({
                                title: 'Proposal deleted successfully.',
                                icon: 'success',
                                customClass: swalCustomClasses
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: 'Error deleting proposal: ' + response,
                                icon: null,
                                customClass: swalCustomClasses
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            title: 'AJAX Error!',
                            text: 'An error occurred while deleting the proposal: ' + error,
                            icon: null,
                            customClass: swalCustomClasses
                        });
                    }
                });
            }
        });
    });
// -----        Update Proposal Function      ------ //
    $('#editProposalForm').submit(function(event) {
        event.preventDefault();
        var formData = $(this).serialize();
        $.ajax({
            url: './create/process_proposals.php',
            type: 'POST',
            data: formData,
            dataType: 'text',
            success: function(response) {
                if (response.trim() === "success") {
                    Swal.fire({
                        title: 'Proposal updated successfully!',
                        icon: 'success',
                        customClass: swalCustomClasses
                    }).then(() => {
                        $('#editProposalModal').hide(); 
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Update Error!',
                        text: "Error updating proposal: " + response,
                        icon: null,
                        customClass: swalCustomClasses
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX error:", status, error, xhr.responseText);
                 
                 Swal.fire({
                    title: 'AJAX Error!',
                    text: "An error occurred while updating the proposal: " + error,
                    icon: null,
                    customClass: swalCustomClasses
                });
            }
        });
    });
});