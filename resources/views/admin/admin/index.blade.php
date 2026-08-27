@extends('admin.layouts.main')
@section('title')
Users
@endsection
@section('content')


<?php $sessionUser = auth()->user();?>
<!-- Content -->

<div class="page-title">
    <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
            <h3>Users</h3>
        </div>
        <div class="col-12 col-md-6 order-md-2 order-first">
            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-sm-end">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="admin/dashboard" class="pjax">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Users</li>
                </ol>
            </nav>
        </div>
    </div>
</div>
<!-- Invoice List Table -->
 <div class="section">
  <div class="card">
  <div class="card-header d-flex align-items-center justify-content-between">
  <h5 class="card-title mb-0">Users</h5>
  @if($sessionUser->hasPermission('admin/user/create'))
    <a href="admin/user/create" class="btn btn-primary d-sm-inline-block pjax">Create</a>
  @endif
</div>

<div class="card-body">
  <div class="card-datatable table-responsive">
    <table class="datatable-list-table table border-top" id="data-table">
      <thead>
        <tr>
          <th>Id</th>
          <th>Name</th>
          <th>Email</th>
          <th>Status</th>
            @if($sessionUser->hasPermission('admin/user/view') || $sessionUser->hasPermission('admin/user/update') || $sessionUser->hasPermission('admin/user/delete'))
          <th>Actions</th>
          @endif
        </tr>
      </thead>
    </table>
  </div>
</div>

  </div>
</div>
<!-- / Content -->

<!--Bootstrap Tables-->

<!--Bootstrap Tables-->

@endsection
@push('scripts')
<script>
  documentReady(function() {
    const hasUpdatePermission = {!! json_encode(
        $sessionUser->hasPermission('admin/user/view') ||
        $sessionUser->hasPermission('admin/user/update') ||
        $sessionUser->hasPermission('admin/user/delete')
    ) !!};

    datatableObj = $('#data-table').DataTable({
     pageLength: 25, 
     lengthMenu: [25, 50, 100],
      ajax: {
        url: '{{ route("admin/user/list") }}',
        method: 'post',
        dataSrc: 'data',
        data: {
          '_token': '{{ csrf_token() }}'
        },
      },
      columns: [
        { data: "id", responsivePriority: 6 },
        { data: "first_name", responsivePriority: 4 },
        { data: "email", responsivePriority: 4 },
        { data: "status", responsivePriority: 4 },
        ...(hasUpdatePermission ? [{
          data: "action",
          bSortable: false,
          responsivePriority: 1
        }] : [])
      ],
      responsive: true,
      serverSide: true,
      order: [[0, "desc"]]
    });
  });
</script>
@endpush
