@extends('company.layouts.main')
@section('title')
Team Members
@endsection
@section('content')

<?php $sessionUser = auth()->user(); ?>
<div class="page-title">
    <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
            <h3>Team Members</h3>
        </div>
        <div class="col-12 col-md-6 order-md-2 order-first">
            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-sm-end">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('company/dashboard') }}" class="pjax">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Team Members</li>
                </ol>
            </nav>
        </div>
    </div>
</div>      

<!-- Content -->
<section class="section">
    <div class="card">
        <div class="card-header justify-content-between d-flex align-items-center flex-wrap gap-2 pb-3">
            <h5 class="card-title">Team Members</h5>
            <div class="d-flex flex-wrap gap-2 align-items-end">
                @if($sessionUser->hasPermission('company/team-member/create'))
                <a href="company/team-member/create" class="btn btn-primary d-sm-inline-block pjax" style="float: inline-end;">Create</a>
                @endif
            </div>
        </div>
        
        <div class="card-body dataTable-container">
            <table class="datatable-list-table table border-top" id="data-table">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</section>

@endsection
@push('scripts')
<script>
    $(document).ready(function () {
        datatableObj = $('#data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("company/team-member/list") }}',
                type: 'POST',
                data: function (d) {
                    d._token = '{{ csrf_token() }}';
                }
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'first_name', name: 'first_name' },
                { data: 'email', name: 'email' },
                { data: 'status_badge', name: 'status', orderable: false, searchable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ]
        });
    });
</script>
@endpush
