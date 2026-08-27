@extends('admin.layouts.main')
@section('title')
Log
@endsection
@section('content')

<!-- Content -->
<!-- Ajax Sourced Server-side -->
<!-- Invoice List Table -->
<div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Account Log</h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-sm-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="admin/dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Log</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

<div class="section">
  <div class="card">
    <div class="card-header">
      <h5 class="card-title">Log / List</h5>
    </div>
    <div class="card-body dataTable-container">
        <table class="datatable-list-table table border-top" id="data-table">
          <thead>
            <tr>
              <th>Created At</th>
              <th>Client</th>
              <th>Location</th>
              <th>IP</th>
              <th>Type</th>
            </tr>
          </thead>
        </table>
    </div>
  </div>
</div>
<!-- / Content -->

<!--Bootstrap Tables-->
@endsection
@push('scripts')
<script>
  documentReady(function() {
    datatableObj = $('#data-table').DataTable({
         pageLength: 25, 
         lengthMenu: [25, 50, 100],
         stateSave :true,
      ajax: {
        url: '{{route("admin/account/log-list")}}',
        method: 'post',
        dataSrc: 'data',
        data: {
          '_token': CSRF_TOKEN
        },
      },
      columns: [

        {
          data: "created_at",
          responsivePriority: 4
        },
        {
          data: "client",
          responsivePriority: 6
        },
        {
          data: "location",
          responsivePriority: 4,
          orderable: false
        },
        {
          data: "ip",
          responsivePriority: 4
        },
        {
          data: "type",
          responsivePriority: 4
        },

      ],
      responsive: true,
      serverSide: true,
      "order": [
        [0, "desc"]
      ]
    });
  });
</script>
@endpush