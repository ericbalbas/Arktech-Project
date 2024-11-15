<?php

$data = $controller->tableConfig();
list($sql, $totalRecords) = $data;
// echo $sql;
// echo $totalRecords;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Automation</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel=" stylesheet" href="assets/css/index.css?v=<?= date('YmdHis') ?>">
    <?= $cpt::head() ?>
</head>

<body>
    <!-- Default header -->
    <?= $header->displayHeader($title, 1, $previousLink, $version, 1, [], null, 1) ?>

    <!-- Task Master Data Table View -->
    <div class="container-fluid">
        <!-- table controllers -->
        <div class="col-md-12">
            <div class="custom-flex">
                <span></span>
                <span>
                    <button class="btn btns btn-primary btn-sm" id="createTask">Create Automation</button>
                </span>
            </div>
        </div>
        <!-- data table  -->
        <div class="col-md-12">
            <span>
                <h5>Total Records: <?= $totalRecords ?></h5>
            </span>
            <table id="mainTableId" class="table table-bordered table-striped table-condensed">
                <thead class="">
                    <th class="text-center"></th>
                    <th class="text-center align-middle">PartNumber</th>
                    <th class="w3-center align-middle">Nesting <br> Drawing</th>
                    <th class="w3-center align-middle">Nesting <br> Program</th>
                    <th class="w3-center align-middle">Quantity</th>
                    <th class="w3-center align-middle">Conditions</th>
                    <th class="w3-center align-middle">Material</th>
                    <th class="w3-center align-middle">Thickness</th>
                    <th class="w3-center align-middle">Size</th>
                    <th class="w3-center align-middle">Automator</th>
                    <th class="w3-center align-middle"></th>
                </thead>

                <tfoot>
                    <th class="w3-center"></th>
                    <th class="w3-center"></th>
                    <th class="w3-center"></th>
                    <th class="w3-center"></th>
                    <th class="w3-center"></th>
                    <th class="w3-center"></th>
                    <th class="w3-center"></th>
                    <th class="w3-center"></th>
                    <th class="w3-center"></th>
                    <th class="w3-center"></th>
                    <th class="w3-center"></th>
                </tfoot>

            </table>
        </div>

        <!-- Create Task Modal -->
        <div id='modal-izi'><span class='izimodal-content'></span></div>
    </div>

</body>

</html>
<?= $cpt::script() ?>
<script>
    // const viewTaskDetails = (taskId) => {
    //     const office = <?php echo $currentoffice; ?>;
    //     $("#modal-izi").iziModal({
    //         title: '<i class="fa fa-plus"></i> Task Details',
    //         headerColor: '#1F4788',
    //         subtitle: '<b><?php echo strtoupper(date('F d, Y')); ?></b>',
    //         width: 600,
    //         fullscreen: false,
    //         transitionIn: 'fadeIn',
    //         transitionOut: 'fadeOut',
    //         padding: 20,
    //         radius: 0,
    //         top: 10,
    //         restoreDefaultContent: true,
    //         closeOnEscape: true,
    //         closeButton: true,
    //         overlayClose: false,
    //         onOpening: function(modal) {
    //             modal.startLoading();
    //             $.ajax({
    //                 url: 'view/izi-modal/office_taskDetails.php',
    //                 type: 'POST',
    //                 data: {
    //                     currentOffice: office,
    //                     taskId: taskId,
    //                 },
    //                 success: function(data) {
    //                     $(".izimodal-content").html(data);
    //                     modal.stopLoading();
    //                 }
    //             });
    //         },
    //         onClosed: function(modal) {
    //             // $("#modal-izi").off("click");
    //             // $("#editTask").off("click");
    //             // $("#modal-izi").off("toggle");
    //             // $("#modal-izi").off("submit");
    //             $("#modal-izi").iziModal("destroy");
    //         }
    //     });



    //     $("#modal-izi").iziModal("open");
    //     $("#modal-izi").off("click", "#editTask");
    //     // $("#modal-izi").off("toggle");
    //     // $("#modal-izi").off("submit");

    //     $("#modal-izi").on("click", "#editTask", function() {
    //         // Toggle the readonly attribute of '.changeable' elements
    //         $('.changeable').each(function() {
    //             const currentReadonly = $(this).attr('readonly') === 'readonly'; // Check if it's readonly
    //             $(this).attr('readonly', !currentReadonly); // Toggle readonly state
    //         });

    //         // Update the button text and class based on the new state
    //         const isEditable = $('.changeable').first().attr('readonly') !== 'readonly'; // Check if it's now editable
    //         const newText = isEditable ? 'Update Task' : 'Edit Task';
    //         const newClass = isEditable ? 'btn btn-primary btn-lg' : 'btn btn-warning btn-lg';
    //         console.log(isEditable);
    //         $("#editTask").text(newText) // Change the button text
    //             .removeClass('btn btn-primary btn-lg btn-warning btn-lg') // Remove both possible classes
    //             .addClass(newClass); // Add the new class

    //         // Set focus on the task name if the fields are editable
    //         if (isEditable) {
    //             $("#taskNameValue").focus();
    //         }

    //         // Submit the form if 'Update Task' is clicked
    //         if (!isEditable) {
    //             $('#formUpdate').submit(); // Only submit when toggling to 'Update Task'
    //         }

    //         // Toggle the visibility of '.taskType' elements
    //         $('.taskType').toggle();
    //     });
    // }

    $(document).ready(function() {

        // Function to get URL parameter value by name
        function getUrlParam(name) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(name);
        }

        // Function to remove a parameter from the URL
        function removeUrlParam(name) {
            const url = new URL(window.location);
            url.searchParams.delete(name);
            window.history.replaceState({}, document.title, url);
        }

        // Define alert configurations based on type
        const alertMap = {
            'success': {
                title: 'Success!',
                text: 'Your action was completed successfully.',
                icon: 'success',
                confirmButtonText: 'OK'
            },
            'update': {
                title: 'Updated!',
                text: 'The record has been updated successfully.',
                icon: 'success',
                confirmButtonText: 'OK'
            },
            'delete': {
                title: 'Deleted!',
                text: 'The record has been deleted.',
                icon: 'warning',
                confirmButtonText: 'OK'
            },
            'error': {
                title: 'Error!',
                text: 'An error occurred. Please try again.',
                icon: 'error',
                confirmButtonText: 'Retry'
            }
        };

        // Get alert type from URL parameter
        const alertType = getUrlParam('alert');

        // Show SweetAlert based on the alert type if it exists in the map
        if (alertType && alertMap[alertType]) {
            Swal.fire(alertMap[alertType]).then(() => {
                // Remove the alert parameter from URL after showing the alert
                removeUrlParam('alert');
            });
        }


        const sqlData = "<?php echo $sql; ?>";
        const totalRecords = "<?php echo $totalRecords; ?>";

        const dataTable = $('#mainTableId').DataTable({
            // "stripeClasses": ['odd-row', 'even-row'], // Optional, you can define custom classes for striping
            "searching": false,
            "processing": true,
            "ordering": false,
            "serverSide": true,
            "bInfo": false,
            "sDom": "lrti",
            "bLengthChange": false,
            "ajax": {
                url: "../Auto Finish Booking/HttpRequest/receiver.php?v=<?php echo date('YmdHis') ?>", // json datasource
                type: "POST", // method  , by default get
                data: {
                    "sqlData": sqlData, // SQL Query POST
                    "totalRecords": totalRecords,
                    "type": "index",
                },
                error: function(data) { // error handling
                    console.log(data.responseText)
                    $(".mainTableId-error").html("");
                    $("#mainTableId").append('<tbody class="mainTableId-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                    $("#mainTableId_processing").css("display", "none");
                }
            },
            "createdRow": function(row, data, index) {
                $(row).addClass("w3-hover-grey rowClass");
                $(row).click(function() {
                    $(".rowClass").removeClass("w3-deep-orange");
                    $(this).addClass("w3-deep-orange");
                });
            },
            "columnDefs": [{
                "targets": [0, 1, 4, 5, 6, 7, 8, 9, 10], // 6th column (0-based index)
                "render": function(data, type, row, meta) {
                    // Return centered content with custom HTML
                    return '<div style="text-align: center; font-size: .8rem !important;">' + data + '</div>';
                }
            }, {
                "targets": [3], // 6th column (0-based index)
                "render": function(data, type, row, meta) {
                    // Return centered content with custom HTML
                    let bg = data.includes('Non') ? 'w3-green' : 'w3-orange';
                    return `<div class="w3-center" style="margin-top: 5px !important;"><span class="label badge-pill ${bg} w3-center">${data}<span></div>`;
                }
            }, {
                "targets": [2], // 6th column (0-based index)
                "render": function(data, type, row, meta) {
                    // Return centered content with custom HTML, and apply ellipsis for overflowed text
                    return data
                },
                "className": "w3-center ellipsis" // Apply class for centering and ellipsis
            }],
            fixedColumns: false,
            deferRender: true,
            scrollY: 450,
            scroller: {
                loadingIndicator: true
            },
            stateSave: false
        });

        const openCreateAutomation = () => {
            $("#modal-izi").iziModal({
                title: '<i class="fa fa-plus"></i> Create Task',
                headerColor: '#1F4788',
                subtitle: '<b><?php echo strtoupper(date('F d, Y')); ?></b>',
                width: 1200,
                fullscreen: false,
                transitionIn: 'fadeIn',
                transitionOut: 'fadeOut',
                padding: 20,
                radius: 0,
                top: 10,
                restoreDefaultContent: true,
                closeOnEscape: true,
                closeButton: true,
                overlayClose: false,
                onOpening: function(modal) {
                    modal.startLoading();
                    $.ajax({
                        url: 'view/izi-modal/createAutomations.php',
                        type: 'POST',
                        data: {
                            type: 1,
                        },
                        success: function(data) {
                            $(".izimodal-content").html(data);
                            modal.stopLoading();
                        }
                    });
                },
                onClosed: function(modal) {
                    $("#modal-izi").iziModal("destroy");
                }
            });

            $("#modal-izi").iziModal("open");
        }

        $("#createTask").click(() => openCreateAutomation());
    });
</script>