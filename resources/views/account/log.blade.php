@extends('layouts.main')
@section('title')
Log
@endsection
@section('content')



<!-- Content -->
<div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Account Log</h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-sm-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="contractor/document" class="pjax">Dashboard</a></li>
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
    <div class="card-body">
            <div class="card-body dataTable-container">
        <table class="datatable-list-table table border-to " id="data-table">
          <thead>
            <tr>
                <th>Date</th>
                <th>Device</th>
                <th>Location </th>
                <th>Ip Address</th>
                <th>Type</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>
<!--Bootstrap Tables-->
@endsection
@push('scripts')
<script>
//       app.addCSS([
//     'theme/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css',
//     'theme/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css'
//   ])
//   app.addJS(['theme/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js']);
    documentReady(function() {
        datatableObj = $('#data-table').DataTable({
         pageLength: 25,    
         lengthMenu: [25, 50, 100],
         stateSave :true,
            ajax: {
                url: '{{route("account/log-list")}}',
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

