@extends('admin.layouts.main')

@section('title')
Export Data
@endsection 

@section('content')

<div class="page-title">
    <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
            <h3>Export Data </h3>
        </div>
        <div class="col-12 col-md-6 order-md-2 order-first">
            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-sm-end">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="admin/dashboard" class="pjax">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Export Data</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<section class="section">
    <div class="card">
        <div class="card-header ">
            <h5 class="card-title">
            Generate Profile Data
            </h5>
        </div>

        <div class="d-flex flex-wrap flex-wrap gap-2 align-items-end flex-wrap mb-4 px-4">
            <div class="form-group mb-0">
                <label class="body">Contactor Status</label>
                <select class="form-select" name="status" id="status">
                    <option value="">All</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>

            <div class="form-group mb-0">
                <label class="body">Document Status</label>
                <select class="form-select" name="status" id="docStatus">
                    <option value="">All</option>
                    <option value="1">Pending</option>
                    <option value="5">Active</option>
                    <option value="2">Expiring Soon</option>
                    <option value="3">Expired</option>
                    <option value="4">Rejected</option>
                    <!-- <option value="0">Missing</option> -->
                </select>
            </div>
            
            <div class="form-group mb-0">
                <label class="body">Company</label>
                <select class="form-select" name="status" id="company_name">
                    <option value="">All</option>
                    @foreach($company as $company)
                    <option value="{{$company->id}}">{{$company->company_name}}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn btn-primary" id="generate">Generate</button>

            <div class="mt-3  d-flex flex-wrap gap-1 d-none" id="exportButton">
                <button class="btn btn-success" id="export-csv">Export as CSV</button>
                <button class="btn btn-secondary" id="export-pdf">Export as PDF</button>
            </div>
        </div>

        <div class="card-body dataTable-container d-none" id="data-body">
            <table class="datatable-list-table table border-top" id="data-table">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Business Name</th>
                        <th>Company Name</th>
                        <th>Status</th>
                        <th>Total Documents</th>
                        <th>Compliance Status</th>
                        <th>Document Name</th>
                        <th>Document Status</th>
                        <th>Expired Date</th>
                        <th>Last Activity Date</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</section>

<section class="section">
    <div class="card">
        <div class="card-header ">
            <h5 class="card-title">
            Export Documents
            </h5>
        </div>

        <div class="d-flex flex-wrap flex-wrap gap-2 align-items-end flex-wrap mb-4 px-4">
            <div class="form-group mb-0">
                <label class="body">Contactor Status</label>
                <select class="form-select" name="status" id="status1">
                    <option value="">All</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>

            <div class="form-group mb-0">
                <label class="body">Document Status</label>
                <select class="form-select" name="status" id="docStatus1">
                    <option value="">All</option>
                    <option value="1">Pending</option>
                    <option value="5">Active</option>
                    <option value="2">Expiring Soon</option>
                    <option value="3">Expired</option>
                    <option value="4">Rejected</option>
                    <!-- <option value="0">Missing</option> -->
                </select>
            </div>
            
            <div class="form-group mb-0">
                <label class="body">Company</label>
                <select class="form-select" name="status" id="company_name1">
                    <option value="">All</option>
                    @foreach($companyList as $company)
                    <option value="{{$company->id}}">{{$company->company_name}}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group mb-0">
                <label class="body">Contractors</label>
                <select class="form-select" name="contractor" id="contractor_dropdown">
                    <option value="">All</option>
                    @foreach($contractorList as $contractor)
                        <option value="{{ $contractor->id }}" data-company-id="{{ $contractor->company_id }}">
                            {{ $contractor->first_name. ' '.  $contractor->last_name  }}
                        </option>
                    @endforeach
                </select>
            </div>
            

            <button class="btn btn-primary" id="exportDocuments">Export Documents</button>
   
        </div>

    </div>
</section>

    <!-- Export Progress Modal -->
    <div class="modal fade" id="exportProgressModal" tabindex="-1" aria-labelledby="exportProgressLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportProgressLabel">Exporting Documents</h5>
                    <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="progress progress-primary mb-4">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 0%;" 
                             aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">)%
                        </div>
                    </div>
                    <div id="exportStatusMessage" class="mt-2 text-success d-none">
                        ✅ Zip created successfully!
                    </div>
                    <div id="exportErrorMessage" class="mt-2 text-danger d-none">
                        ❌ Failed to export documents.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="closeExportModal" class="btn btn-secondary" data-bs-dismiss="modal" disabled>Close</button>
                </div>
            </div>
        </div>
    </div>


@endsection

@push('scripts')
<script>
documentReady(function() {
    var datatableObj = $('#data-table').DataTable({
         pageLength: 25, 
         lengthMenu: [25, 50, 100],
         stateSave :true,
        ajax: {
            url: '{{ route("admin/contractor/report-list") }}',
            method: 'POST',
            dataSrc: 'data',
            data: function(d) {
                d.status = $('#status').val();  
                d.docStatus = $('#docStatus').val();
                d.company_name = $('#company_name').val();
                d._token = CSRF_TOKEN;  
            }
        },
        columns: [
            {
                data: "id",
                responsivePriority: 4
            },
            {
                data: "first_name",
                responsivePriority: 4
            },
            {
                data: "email",
                responsivePriority: 2
            },
            {
                data: "business_name",
                responsivePriority: 2
            },
            {
                data: "company_id",
                responsivePriority: 2
            },
            {
                data: "status",
                responsivePriority: 2
            },
            {
                data: "document_count",
                responsivePriority: 4,
                bSortable: false,
            },
            {
                data: "compliance_status",
                responsivePriority: 4,
                bSortable: false,
            },
            {
                data: "document_type",
                responsivePriority: 4,
                bSortable: false,
            },
            {
                data: "document_status",
                responsivePriority: 4,
                bSortable: false,
            },
            {
                data: "document_expired_at",
                responsivePriority: 4,
                bSortable: false,
            },
            {
                data: "latest_document_updated",
                responsivePriority: 3,
                bSortable: false,
            }
        ],
        responsive: true,
        serverSide: true,
        order: [
            [0, "desc"]
        ]
    });

    $('#generate').click(function() {
        $('#data-body').removeClass('d-none'); 
        $('#exportButton').removeClass('d-none');
        datatableObj.ajax.reload();  
    });

    $('#export-csv').click(function(){
        var status = $('#status').val();
        var docStatus = $('#docStatus').val();
        var companyName = $('#company_name').val();
        window.location.href = '{{ route("admin/contractor/report-export") }}' + '?status=' + status +'&docStatus=' + docStatus + '&companyName=' + companyName + '&type=' + 'csv';
    });

    $('#export-pdf').click(function(){
        var status = $('#status').val();
        var docStatus = $('#docStatus').val();
        var companyName = $('#company_name').val();
        window.location.href = '{{ route("admin/contractor/report-export") }}' + '?status=' + status + '&docStatus=' + docStatus + '&companyName=' + companyName + '&type=' + 'pdf';
    });
    let exportCancelled = false;
    let progressInterval = null;

    // $('#exportDocuments').click(function () {
    //     const modalElement = document.getElementById('exportProgressModal');
    //     const exportModal = new bootstrap.Modal(modalElement);
    //     exportModal.show();
    
    //     $('#exportStatusMessage').addClass('d-none');
    //     $('#exportErrorMessage').addClass('d-none');
    //     $('#closeExportModal').prop('disabled', true);
    
    //     // Reset progress bar
    //     $('.progress-bar').css('width', '0%').attr('aria-valuenow', 0).text('0%');
    
    //     var data = {
    //         _token: '{{ csrf_token() }}',
    //         status: $('#status1').val(),
    //         docStatus: $('#docStatus1').val(),
    //         companyName: $('#company_name1').val(),
    //         contractorId: $('#contractor_dropdown').val()
    //     };
    //     //     let progressInterval = setInterval(function () {
    //     //     $.get('{{ url("/admin/contractors/export-progress") }}', function (res) {
    //     //         const percent = res.progress;
    //     //         $('.progress-bar')
    //     //             .css('width', percent + '%')
    //     //             .attr('aria-valuenow', percent)
    //     //             .text(percent + '%');
    //     //     });
    //     // }, 1000);
    //     // Poll progress
    //     var progresswidth = 0;
    //     let progressInterval = setInterval(function () {
    //         if(progresswidth < 95){
    //             $('.progress-bar')
    //                 .css('width', progresswidth + '%')
    //                 .attr('aria-valuenow', progresswidth)
    //                 .text(progresswidth + '%');
    //                 progresswidth = progresswidth +1;
    //         }
    //     }, 200);
    
    //     $.ajax({
    //         url: '{{ route("admin/contractors/export-documents") }}',
    //         type: 'POST',
    //         data: data,
    //         success: function (response) {
    //             clearInterval(progressInterval);
    //             $('.progress-bar').css('width', '100%').attr('aria-valuenow', 100).text('100%');
    //             $('#exportStatusMessage').removeClass('d-none');
    //             $('#closeExportModal').prop('disabled', false);
    
    //             var link = document.createElement('a');
    //             link.href = response.file_url;
    //             link.download = response.file_name;
    //             document.body.appendChild(link);
    //             link.click();
    //             document.body.removeChild(link);
    
    //             setTimeout(() => {
    //                 bootstrap.Modal.getInstance(modalElement).hide();
    //             }, 2000);
    //         },
    //         error: function (xhr) {
    //             clearInterval(progressInterval);
    //             $('#closeExportModal').prop('disabled', false);
    
    //             if (xhr.status === 404 && xhr.responseJSON?.warning) {
    //                 $('#exportErrorMessage')
    //                     .removeClass('d-none')
    //                     .text(xhr.responseJSON.warning)
    //                     .removeClass('text-danger')
    //                     .addClass('text-dark');
    //             } else {
    //                 $('#exportErrorMessage')
    //                     .removeClass('d-none')
    //                     .text('An unexpected error occurred.')
    //                     .removeClass('text-dark')
    //                     .addClass('text-danger');
    //             }
    
    //             setTimeout(() => {
    //                 bootstrap.Modal.getInstance(modalElement).hide();
    //             }, 2000);
    //         }
    //     });
    // });
    $('#exportProgressModal').on('hide.bs.modal', function () {
        exportCancelled = true;
        clearInterval(progressInterval);
    });

    $('#exportDocuments').click(function () {
        const modalElement = document.getElementById('exportProgressModal');
        const exportModal = new bootstrap.Modal(modalElement);
        exportModal.show();

        exportCancelled = false; // Reset on new export
        $('#exportStatusMessage').addClass('d-none');
        $('#exportErrorMessage').addClass('d-none');
        $('#closeExportModal').prop('disabled', false);

        // Reset progress bar
        $('.progress-bar').css('width', '0%').attr('aria-valuenow', 0).text('0%');

        const data = {
            _token: '{{ csrf_token() }}',
            status: $('#status1').val(),
            docStatus: $('#docStatus1').val(),
            companyName: $('#company_name1').val(),
            contractorId: $('#contractor_dropdown').val()
        };

        let progresswidth = 0;
        progressInterval = setInterval(function () {
            if (exportCancelled) {
                clearInterval(progressInterval);
                return;
            }

            if (progresswidth < 95) {
                $('.progress-bar')
                    .css('width', progresswidth + '%')
                    .attr('aria-valuenow', progresswidth)
                    .text(progresswidth + '%');
                progresswidth += 1;
            }
        }, 200);

        $.ajax({
            url: '{{ route("admin/contractors/export-documents") }}',
            type: 'POST',
            data: data,
            success: function (response) {
                clearInterval(progressInterval);

                if (exportCancelled) return; // Skip download if user canceled

                $('.progress-bar').css('width', '100%').attr('aria-valuenow', 100).text('100%');
                $('#exportStatusMessage').removeClass('d-none');
                $('#closeExportModal').prop('disabled', false);

                const link = document.createElement('a');
                link.href = response.file_url;
                link.download = response.file_name;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                setTimeout(() => {
                    bootstrap.Modal.getInstance(modalElement).hide();
                }, 2000);
            },
            error: function (xhr) {
                clearInterval(progressInterval);

                if (exportCancelled) return;

                $('#closeExportModal').prop('disabled', false);

                if (xhr.status === 404 && xhr.responseJSON?.warning) {
                    $('#exportErrorMessage')
                        .removeClass('d-none')
                        .text(xhr.responseJSON.warning)
                        .removeClass('text-danger')
                        .addClass('text-dark');
                } else {
                    $('#exportErrorMessage')
                        .removeClass('d-none')
                        .text('An unexpected error occurred.')
                        .removeClass('text-dark')
                        .addClass('text-danger');
                }

                setTimeout(() => {
                    bootstrap.Modal.getInstance(modalElement).hide();
                }, 2000);
            }
        });
    });

    // for a selected contractor based on company
     $('#company_name1').on('change', function() {
        var selectedCompany = $(this).val();

        $('#contractor_dropdown option').each(function() {
            var companyId = $(this).data('company-id');

            if (!selectedCompany) {
                // Show all options if company is "All"
                $(this).show();
            } else if (companyId == selectedCompany) {
                $(this).show();
            } else if ($(this).val() == "") {
                // Keep "All" option visible always
                $(this).show();
            } else {
                $(this).hide();
            }
        });

        // Reset contractor dropdown to "All"
        $('#contractor_dropdown').val('');
    });
});
</script>
@endpush
