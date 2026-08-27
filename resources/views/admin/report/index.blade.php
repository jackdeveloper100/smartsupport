@extends('admin.layouts.main')

@section('title')
    Report
@endsection

@section('content')

<div class="page-title">
    <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
            <h3>Report </h3>
        </div>
        <div class="col-12 col-md-6 order-md-2 order-first">
            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-sm-end">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="admin/dashboard" class="pjax">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Report Page</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<section class="section">
    <div class="card">
        <div class="card-header ">
            <h5 class="card-title">
            Generate Report
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

});
</script>
@endpush
