<script type="text/javascript">
    $(document).ready(function() {

        function getTaxonomiesIndexPage() {
            var data = {
                category_type: $('#category_type').val()
            };
            $.ajax({
                method: "GET",
                dataType: "html",
                url: '/taxonomies-ajax-index-page',
                data: data,
                async: false,
                success: function(result) {
                    $('.taxonomy_body').html(result);
                }
            });
        }

        function initializeTaxonomyDataTable() {
            //Category table
            if ($('#category_table').length) {
                var category_type = $('#category_type').val();
                category_table = $('#category_table').DataTable({
                    processing: true,
                    serverSide: true,
                    buttons: [{
                            extend: 'print',
                            text: 'Print',
                            className: 'buttons-print',
                            exportOptions: {
                                columns: ':not(.no-print)'
                            },
                            customize: function(win) {
                                logPrintEvent();

                                $(win.document.body).find('h1').remove();

                                var defaultTitle = $('title').text();
                                var reportTitle = defaultTitle.split(' - ')[0] + ' Report';

                                var pageBreakAdded = false;

                                var header = $(`
                                <header style="padding: 10px; z-index: 1000;">
                                    <div class="row header" style="display: flex; justify-content: space-between; align-items: center;">
                                        <div class="col-md-2 mt-3">
                                            <img src="{{ asset('dummy/paklogo4.png') }}" width="100px" />
                                        </div>
                                        <div class="col-md-8" style="text-align: center;">
                                            <h4 style="font-weight: bold;">ARMED FORCES MEDICAL STORES LABORATORY</h4>
                                            <hr style="margin: 5px 0;"> <!-- Add horizontal line here -->
                                            <h5 style="font-weight: bold;">${reportTitle}</h5> <!-- Add dynamic report title here -->
                                        </div>
                                        <div class="col-md-2 mt-3" style="text-align: end;">
                                            <img src="{{ asset('dummy/AFMS LOGO-01.png') }}" width="110px" />
                                           
                                        </div>
                                    </div>
                                </header>
                            `);

                                $(win.document.body).prepend(header);

                                $.get('/get-footer', function(footerContent) {
                                    $(win.document.body).append(footerContent);
                                });

                                var currentPage = 0;
                                var rowCount = 0;

                                $(win.document.body).find('table').addClass('print-table');
                                $(win.document.body).find('.print-table tr').each(function(
                                    index) {
                                    rowCount++;
                                    if (rowCount % 25 === 0) {
                                        currentPage++;
                                        $(this).after('<div class="page-break"></div>');
                                        pageBreakAdded =
                                            true;
                                    }
                                });

                                if (pageBreakAdded) {
                                    header.css('position', 'fixed');
                                    header.css('left', '0');
                                    header.css('right', '0');
                                    header.css('background-color', '#fff');
                                    $('<style>.print-table { position: relative; top: 150px; bottom: 150px; }</style>')
                                        .appendTo(win.document.head);

                                }

                            }

                        },
                        {
                            extend: 'excel',
                            text: 'Export to Excel',
                            className: 'buttons-excel',
                            exportOptions: {
                                columns: ':not(.no-print)'
                            }
                        },
                        {
                            extend: 'pdf',
                            text: 'Export to PDF',
                            className: 'buttons-pdf',
                            exportOptions: {
                                columns: ':not(.no-print)'
                            },
                        },
                        {
                            extend: 'csv',
                            text: 'Export to CSV',
                            className: 'buttons-csv',
                            exportOptions: {
                                columns: ':not(.no-print)'
                            }
                        }, 'colvis'
                    ],
                    ajax: '/taxonomies?type=' + category_type,
                    columns: [{
                            data: 'name',
                            name: 'name'
                        },
                        @if ($cat_code_enabled)
                            {
                                data: 'short_code',
                                name: 'short_code'
                            },
                        @endif {
                            data: 'description',
                            name: 'description'
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        },
                    ],
                });
            }
        }

        @if (empty(request()->get('type')))
            getTaxonomiesIndexPage();
        @endif

        initializeTaxonomyDataTable();

    });

    function logPrintEvent() {
        var defaultTitle = $('title').text();
        var reportTitle = defaultTitle.split(' - ')[0] + ' Report';
        var randomID = Math.floor(Math.random() * 100000);
        var documentID = reportTitle + ' - ' + randomID;

        $.ajax({
            url: '/print-event',
            method: 'post',
            data: {
                documentID: documentID,
                printedModule: 'Taxonomies'
            },
            success: function(response) {},
            error: function(xhr, status, error) {
                console.error('Error logging print event:', error);
            }
        });
    }
    $(document).on('submit', 'form#category_add_form', function(e) {
        e.preventDefault();
        var form = $(this);
        var data = form.serialize();

        $.ajax({
            method: 'POST',
            url: $(this).attr('action'),
            dataType: 'json',
            data: data,
            beforeSend: function(xhr) {
                __disable_submit_button(form.find('button[type="submit"]'));
            },
            success: function(result) {
                if (result.success === true) {
                    $('div.category_modal').modal('hide');
                    toastr.success(result.msg);
                    if (typeof category_table !== 'undefined') {
                        category_table.ajax.reload();
                    }

                    var evt = new CustomEvent("categoryAdded", {
                        detail: result.data
                    });
                    window.dispatchEvent(evt);

                    //event can be listened as
                    //window.addEventListener("categoryAdded", function(evt) {}
                } else {
                    toastr.error(result.msg);
                }
            },
        });
    });
    $(document).on('click', 'button.edit_category_button', function() {
        $('div.category_modal').load($(this).data('href'), function() {
            $(this).modal('show');

            $('form#category_edit_form').submit(function(e) {
                e.preventDefault();
                var form = $(this);
                var data = form.serialize();

                $.ajax({
                    method: 'POST',
                    url: $(this).attr('action'),
                    dataType: 'json',
                    data: data,
                    beforeSend: function(xhr) {
                        __disable_submit_button(form.find('button[type="submit"]'));
                    },
                    success: function(result) {
                        if (result.success === true) {
                            $('div.category_modal').modal('hide');
                            toastr.success(result.msg);
                            category_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            });
        });
    });

    $(document).on('click', 'button.delete_category_button', function() {
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(willDelete => {
            if (willDelete) {
                var href = $(this).data('href');
                var data = $(this).serialize();

                $.ajax({
                    method: 'DELETE',
                    url: href,
                    dataType: 'json',
                    data: data,
                    success: function(result) {
                        if (result.success === true) {
                            toastr.success(result.msg);
                            category_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            }
        });
    });
</script>
