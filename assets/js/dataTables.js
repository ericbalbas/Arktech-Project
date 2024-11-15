<?php

?>
<script>
        const viewTaskDetails = (taskId) => {
            const office = <?php echo $currentoffice; ?>;
            $("#modal-izi").iziModal({
                title: '<i class="fa fa-plus"></i> Task Details',
                headerColor: '#1F4788',
                subtitle: '<b><?php echo strtoupper(date('F d, Y')); ?></b>',
                width: 600,
                fullscreen: false,
                transitionIn: 'comingIn',
                transitionOut: 'comingOut',
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
                        url: 'view/izi-modal/office_taskDetails.php',
                        type: 'POST',
                        data: {
                            currentOffice: office,
                            taskId: taskId,
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

            $("#modal-izi").on("click", "#editTask", function() {
                $('.changeable').attr('readonly', function(index, attr) {
                    const text = attr ? 'Update Task' : 'Edit Task';
                    const color = attr ? 'btn btn-primary btn-lg' : 'btn btn-warning btn-lg';
                    $("#editTask").text("").text(text).removeClass('btn btn-warning btn-lg').addClass(color);
                    $("#taskNameValue").focus();
                    if (!attr) {
                        $('#formUpdate').submit();
                    }

                    return attr ? false : true; // Toggles between readonly and editable
                });

                $('.taskType').toggle(); // Toggles visibility of all taskType elements
            });
        }

    $(document).ready(function() {
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
                url: "../GWS Office/ajax/receiver.php?v=<?php echo date('YmdHis') ?>", // json datasource
                type: "POST", // method  , by default get
                data: {
                    "sqlData": sqlData, // SQL Query POST
                    "totalRecords": totalRecords,
                    "type": "index",
                },
                error: function(data) { // error handling
                    console.log(data)
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
                "targets": [1, 4, 5, 6], // 6th column (0-based index)
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
            fixedColumns: true,
            deferRender: true,
            scrollY: 450,
            scroller: {
                loadingIndicator: true
            },
            stateSave: false
        });

        const openCreateTask = () => {
            const office = <?php echo $currentoffice; ?>;
            $("#modal-izi").iziModal({
                title: '<i class="fa fa-plus"></i> Create Task',
                headerColor: '#1F4788',
                subtitle: '<b><?php echo strtoupper(date('F d, Y')); ?></b>',
                width: 600,
                fullscreen: false,
                transitionIn: 'comingIn',
                transitionOut: 'comingOut',
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
                        url: 'view/izi-modal/office_createTask.php',
                        type: 'POST',
                        data: {
                            currentOffice: office,
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

        $("#createTask").click(() => openCreateTask());
    });
</script>
