@extends('admin.layouts.main')
@section('title')
Pages
@endsection
@section('content')

<?php $sessionUser = auth()->user(); ?>

<!-- Content -->
<div class="page-title">
    <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
            <h3>Pages</h3>
        </div>
        <div class="col-12 col-md-6 order-md-2 order-first">
            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="admin/dashboard" class="pjax">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Pages</li>
                </ol>
            </nav>
        </div>
    </div>
</div>
<!-- Invoice List Table -->
 <div class="section">
  <div class="card">
    <div class="card-header">
      <h5 class="card-title">Pages</h5>
    </div>
    <div class="card-body">
      <div class="card-datatable table">
        <table class="datatables table" id="data-table">
          <thead>
            <tr>
              <th>Id</th>
              <th>Title</th>
               @if($sessionUser->hasPermission('admin/page/update'))
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
@endsection
@push('scripts')
<script>
  documentReady(function() {
        const hasUpdatePermission = {{ json_encode(@$sessionUser->hasPermission('admin/page/update')) }};
    datatableObj = $('#data-table').DataTable({
         pageLength: 25, 
         lengthMenu: [25, 50, 100],
      stateSave: true,
      ajax: {
        url: '{{route("admin/page/list")}}',
        dataSrc: 'data',
        data: {
          '_token': CSRF_TOKEN
        }
      },
      columns: [

        {
          data: "id",
          responsivePriority: 6
        }, //,visible:false
        {
          data: "title",
          responsivePriority: 6
        }, //,visible:false
          ...(hasUpdatePermission ? [
          { data: "action",
            bSortable: false, 
            responsivePriority: 1 
          }] : [])
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