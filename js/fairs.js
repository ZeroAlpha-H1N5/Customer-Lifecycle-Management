$(document).ready(function() {
// -----        Sweet Alert Custom Classes      ------ //
    const swalCustomClasses = {
        popup: 'swal-custom-popup',
        confirmButton: 'swal-custom-confirm-button',
        cancelButton: 'swal-custom-cancel-button',
        denyButton: 'swal-custom-deny-button'
    };
// -----        Add Fairs Modal Elements        ------ //
    var modal = $("#addFairsModal");
    var btn = $("#addFairsButton");
    var closeBtn = $(".close");
    var cancel = $(".cancel-button");
    btn.click(function() {
        modal.show();
    });
    closeBtn.click(function() {
        modal.hide();
    });
    cancel.click(function() {
        modal.hide();
    });
    $(window).click(function(event) {
        if (event.target == modal[0]) {
            modal.hide();
        }
    });
// -----        Edit Fairs Modal Elements       ------ //
    const editModal = $("#editFairsModal");
    const editButtons = $(".editButtonFairs");
    const editForm = $("#editFairForm");
    editButtons.on('click', function() {
        const fairId = $(this).data('fair-id');
        openEditModal(fairId);
        editModal.css('display', 'block');
    });
    closeBtn.on('click', function() {
        editModal.css('display', 'none');
    });
    $(window).on('click', function(event) {
        if (event.target == editModal[0]) {
            editModal.css('display', 'none');
        }
    });
    function openEditModal(fairId) {
    $.ajax({
        url: `./fetch/fetch_fair_data.php?fair_id=${fairId}`,
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data && data.fair_id) {
                $("#fair_id").val(data.fair_id);
                $("#edit_fair_title").val(data.fair_title);
                $("#edit_fair_date_start").val(data.fair_date_start);
                $("#edit_fair_date_end").val(data.fair_date_end);
                $("#edit_fair_venue").val(data.fair_venue);
                $("#edit_fair_desc").val(data.fair_desc);
                $("#edit_fair_remarks").val(data.fair_remarks);
            } else {
                Swal.fire({
                    title: 'Fetch Error',
                    text: "Failed to fetch fair details or received invalid data.",
                    icon: null,
                    customClass: swalCustomClasses
                });
                 editModal.css('display', 'none');
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Error:", textStatus, errorThrown);
             Swal.fire({
                title: 'AJAX Error',
                text: "An error occurred while fetching data: " + errorThrown,
                icon: null,
                customClass: swalCustomClasses
             });
             editModal.css('display', 'none');
        }
    });
}
// -----        Delete Fairs Button Function           ------ //
    $(document).on('click', '.deleteButtonFairs', function() {
        var fairId = parseInt($(this).data('fair-id'), 10);
        Swal.fire({
            title: 'Are you sure?',
            text: "You want to delete this exhibit? This action cannot be undone.",
            icon: null,
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'Cancel',
            customClass: swalCustomClasses 
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: "./delete/delete_exhibit.php",
                    data: { fair_id: fairId, action: "delete_exhibit" },
                    dataType: "json",
                    success: function(response) {
                        console.log("Server response: ", response);
                        if (response.status === "success") {
                            Swal.fire({
                                title: 'Event Deleted Successfully!',
                                icon: 'success',
                                customClass: swalCustomClasses
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                             Swal.fire({
                                title: 'Deletion Failed!',
                                text: response.message || "Failed to delete exhibit!",
                                icon: null,
                                customClass: swalCustomClasses
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX error: ", status, error);
                         Swal.fire({
                            title: 'AJAX Error!',
                            text: "An error occurred while deleting the exhibit: " + error,
                            icon: null,
                            customClass: swalCustomClasses
                        });
                    }
                });

            }
           
        });
    });
// -----        Submit Fairs Button Function           ------ //
    $("#addFairsForm").submit(function(event) {
        event.preventDefault();
        const formname = $("#addFairsForm").serialize();
        $.ajax({
            type: "POST",
            url: "./create/process_fairs.php",
            data: formname,
            success: function(response) {
                Swal.fire({
                    title: 'Event Added Successfully!',
                    icon: 'success',
                    customClass: swalCustomClasses
                }).then(() => {
                    modal.hide();
                    window.location.reload();
                });
            },
            error: function(xhr, status, error) {
                console.error("AJAX error adding event:", status, error);
                Swal.fire({
                    title: 'AJAX Error!',
                    text: "Error adding event: " + error,
                    icon: null,
                    customClass: swalCustomClasses
                }).then(() => {
                     modal.hide();
                });
            }
        });
    });
    // -----        Update Fairs Button Function           ------ //
    editForm.on('submit', function(event) {
        event.preventDefault();
        const formData = $(this).serialize();
        $.ajax({
            url: './update/update_fairs.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    Swal.fire({
                        title: 'Fair updated successfully!',
                        icon: 'success',
                        customClass: swalCustomClasses
                    }).then(() => {
                        editModal.css('display', 'none');
                        window.location.reload(); 
                    });
                } else {
                    Swal.fire({
                        title: 'Update Error',
                        text: 'Error updating fair: ' + (data.message || 'Unknown error.'),
                        icon: null,
                        customClass: swalCustomClasses
                    });
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Error:', textStatus, errorThrown);
                 Swal.fire({
                    title: 'AJAX Error',
                    text: 'An error occurred while updating the fair: ' + errorThrown,
                    icon: null,
                    customClass: swalCustomClasses
                 });
            }
        });
    });
}); 