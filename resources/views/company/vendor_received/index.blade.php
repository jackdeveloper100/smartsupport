@extends('company.layouts.main')

@section('title', 'Outstanding W-9 Requests')

@section('content')
<?php $sessionUser = auth()->user(); ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
            <h3>Received W-9s</h3>
        </div>
        <div class="col-12 col-md-6 order-md-2 order-first">
            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-sm-end">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('company/dashboard') }}" class="pjax">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">W-9 Received</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">W-9 Received</h5> 
        </div>
        <div class="card-body">
            <div class="card-datatable table-responsive">
                <table class="datatable-list-table table border-top" id="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Behalf of</th>
                            <th>Legal Name</th>
                            <th>Entity Type</th>
                            <th>Audit Trail </th>
                            <th>Received</th>
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

    if ($('#data-table').length) {
        $('#data-table').DataTable({
            processing: false,
            serverSide: true,
            lengthMenu: [25, 50, 100],
            stateSave: true,
            ajax: {
                url: '{{ route("company/w9/request/received/list") }}',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' }
            },
           columns: [
            { data: 'id' },
            { data: 'representative_of' },
            { data: 'vendor_company_name' },
            { data: 'entity_type' },
            { data: 'audit_icon', orderable: false, searchable: false }, 
            { data: 'updated_at' },
            { data: 'action', orderable: false, searchable: false }
        ],
            order: [[0, 'desc']],
            responsive: true
        });
    }
    
});
</script>


@endpush
