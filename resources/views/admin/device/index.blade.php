@extends('admin.layouts.main')
@section('title')
Device
@endsection
@section('content')


<!-- Content -->

<div class="page-title">
    <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
            <h3>Device</h3>
        </div>
        <div class="col-12 col-md-6 order-md-2 order-first">
            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="admin/dashboard" class="pjax">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Device</li>
                </ol>
            </nav>
        </div>
    </div>
</div>
<!-- Invoice List Table -->
 <div class="section">
<div class="card">
    <div class="card-header ">
      <h4 class="card-title">Device </h4>
    </div>
    <div class="card-body">
          <div class="card-datatable table-responsive">
        <table class="datatable-list-table table border-top" id="data-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Email</th>
              <th>Client</th>
              <th>IP</th>
              <th>Location</th>
              <th>Last Activity</th>
              <th>Actions</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- / Content -->


<!--Bootstrap Tables-->

@endsection
@push('scripts')
<script>
  documentReady(function() {
           pageLength: 25,  
         lengthMenu: [25, 50, 100],
         stateSave :true,
    datatableObj = $('#data-table').DataTable({
      ajax: {
        url: '{{route("admin/device/list")}}',
        method: 'post',
        dataSrc: 'data',
        data: {
          '_token': CSRF_TOKEN
        },
      },
      columns: [{
          data: "id",
          responsivePriority: 6
        },
        {
          data: "first_name",
          responsivePriority: 2
        },
        {
          data: "email",
          responsivePriority: 2
        },
        {
          data: "client",
          responsivePriority: 4,
          sortable: false
        },
        {
          data: "ip",
          responsivePriority: 3
        },
        {
          data: "location",
          responsivePriority: 2,
          sortable: false
        },
        {
          data: "last_activity",
          responsivePriority: 1,
          sortable: false
        },
        {
          data: "action",
          responsivePriority: 1,
          sortable: false
        },
      ],
      responsive: true,
      serverSide: true,
      "order": [
        [4, "desc"]
      ]
    });
  });
</script>
@endpush