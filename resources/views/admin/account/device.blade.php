@extends('admin.layouts.main')
@section('title')
Device
@endsection
@section('content')


  <!-- Content -->


  <!-- <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Device /</span> List</h4> -->

<!-- Invoice List Table -->

<div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Account Device</h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-sm-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="admin/dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Device</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

<div class="section">
  <div class="card">
    <div class="card-header">
      <h5 class="card-title">Device  / List</h5>
    </div>
    <div class="card-body dataTable-container">
        <table class="datatable-list-table table border-top" id="data-table">
          <thead>
            <tr>
              <th>Id</th>
              <th>Client</th>
              <th>IP</th>
              <th>Location</th>
              <th>Last Activity</th>
              <th>Action</th>
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
        url: '{{route("admin/account/device-list")}}',
        method: 'post',
        dataSrc: 'data',
        data: {
          '_token': CSRF_TOKEN
        },

      },
      columns: [{
          data: "id",
          responsivePriority: 4
        },
        {
          data: "client",
          responsivePriority: 2,
          sortable: false
        },
        {
          data: "ip",
          responsivePriority: 2
        },
        {
          data: "location",
          responsivePriority: 2,
          sortable: false
        },
        {
          data: "last_activity",
          responsivePriority: 2
        },
        {
          data: "action",
          responsivePriority: 2,
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