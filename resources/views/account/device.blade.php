@extends('layouts.main')
@section('title')
Device
@endsection
@section('content')


<div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Account Device</h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-sm-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="contractor/document" class="pjax">Dashboard</a></li>
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
    <div class="card-body">
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
</div>

@endsection
@push('scripts')
<script>
  // app.addCSS([
  //   'theme/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css',
  //   'theme/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css'
  // ])
  // app.addJS(['theme/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js']);
  documentReady(function() {
    datatableObj = $('#data-table').DataTable({
         pageLength: 25, 
         lengthMenu: [25, 50, 100],
         stateSave :true,
      ajax: {
        url: '{{route("account/device-list")}}',
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
        },
        {
          data: "ip",
          responsivePriority: 2
        },
        {
          data: "location",
          responsivePriority: 2,
          bSortable: false,
        },
        {
          data: "last_activity",
          responsivePriority: 2
        },
        {
          data: "action",
          responsivePriority: 2,
          bSortable: false,
        },
      ],
      responsive: true,
      serverSide: true,
      retrieve: true,
      "order": [
        [5, "desc"]
      ]
    });
  });
</script>
@endpush
