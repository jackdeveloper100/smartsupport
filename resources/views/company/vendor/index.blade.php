@extends('company.layouts.main')

@section('title', 'Outstanding W-9 Requests')

@section('content')
<?php $sessionUser = auth()->user(); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
            <h3>Outstanding W-9 Requests</h3>
        </div>
        <div class="col-12 col-md-6 order-md-2 order-first">
            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-sm-end">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('company/dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">W-9 Requests</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Outstanding W-9 Requests</h5>
            @if($sessionUser->hasPermission('company/vendor/send_mail'))
            <a href="javascript:void(0)" onclick="app.showModalView('{{ route('company/request/create') }}')" class="btn btn-primary">
                <i class="fa fa-plus"></i> Request W-9
            </a>
            @endif
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="datatable-list-table table border-top" id="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Behalf of</th>
                            <th>Legal Name</th>
                            <th>Status</th>
                            <th>Email last sent at</th>
                            <th>Request opened at</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#data-table').DataTable({
        processing: false,
        serverSide: true,
        pageLength: 25,
        lengthMenu: [25,50,100],
        // stateSave: true,
        ajax: {
            url: '{{ route("company/request/list") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
        },
        columns: [
            { data: 'id', orderable: true },
            { data: 'representative_of', orderable: true },
            { data: 'vendor_company_name', orderable: true },
            { data: 'request_status', orderable: true },
            { data: 'created_at', orderable: true },
            { data: 'updated_at', orderable: true },
            { data: 'action', orderable: false }
        ],
        order: [[0, 'desc']],
        responsive: true
    });
});
</script>
@endpush
