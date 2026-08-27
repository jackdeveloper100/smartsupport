@extends('admin.layouts.main')
@section('title')
Documnents Type
@endsection
@section('content')

<?php $sessionUser = auth()->user(); ?>
<div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Documents Type</h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-sm-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin/dashboard') }}" class="pjax">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Documents Type</li>
                    </ol>
                </nav>
            </div>
        </div>
</div>
<!-- Content -->
<section class="section">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                <h5 class="card-title">
                Documents Type
                </h5>
                @if($sessionUser->hasPermission('admin/setting/document_type/create'))
                  <a href="admin/setting/document_type/create" class="btn btn-primary d-sm-inline-block  pjax" style="float: inline-end;">Create</a>
                @endif
              </div>
            <div class="card-body dataTable-container">
                    <table class="datatable-list-table table border-top" id="data-table">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Document Name</th>
                                <th>Document Type</th>
                                  @if($sessionUser->hasPermission('admin/setting/document_type/update') || $sessionUser->hasPermission('admin/setting/document_type/delete'))
                                  <th>Actions</th>
                                  @endif
                            </tr>
                        </thead>
                    </table>
                </div>
        </div>

</section>
@endsection
@push('scripts')
<script>
documentReady(function() {
    const hasUpdatePermission = {!! json_encode(
        $sessionUser->hasPermission('admin/setting/document_type/update') ||
        $sessionUser->hasPermission('admin/setting/document_type/delete')
    ) !!};
    
    datatableObj = $('#data-table').DataTable({
         pageLength: 25, 
         lengthMenu: [25, 50, 100],
         stateSave :true,
      ajax: {
        url: '{{route("admin/setting/document_type/list")}}',
        method: 'post',
        dataSrc: 'data',
        data: {
          '_token': CSRF_TOKEN
        }
      },
      columns: [{
          data: "id",
          responsivePriority: 4
        }, //,visible:false
        {
          data: "name",
          responsivePriority: 4
        }, //,visible:false
         {
          data: "type",
          responsivePriority: 4
        }, //,visible:false
         ...(hasUpdatePermission ? [{
          data: "action",
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