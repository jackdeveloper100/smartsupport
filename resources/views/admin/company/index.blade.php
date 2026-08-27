@extends('admin.layouts.main')
@section('title')
Company
@endsection
@section('content')


<?php $sessionUser = auth()->user();?>
<!-- Content -->

<div class="page-title">
    <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
            <h3>Company</h3>
        </div>
        <div class="col-12 col-md-6 order-md-2 order-first">
            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-sm-end">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="admin/dashboard" class="pjax">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Company</li>
                </ol>
            </nav>
        </div>
    </div>
</div>
<!-- Invoice List Table -->
 <div class="section">
  <div class="card">
  <div class="card-header d-flex align-items-center justify-content-between">
  <h5 class="card-title mb-0">Company</h5>
  @if($sessionUser->hasPermission('admin/company/create'))
    <a href="admin/company/create" class="btn btn-primary d-sm-inline-block pjax">Create</a>
  @endif
</div>

<div class="card-body">
  <div class="card-datatable table-responsive">
    <table class="datatable-list-table table border-top moblie_table" id="data-table">
      <thead>
        <tr>
          <th>Id</th>
          <th>Company Name</th>
          <!--<th>Email</th>-->
          <th>Plan Name</th>
          <th>Price</th>
          <th>Status</th>
          <th>Subscription Status</th>
          <th>Expired Date</th>
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
        url: '{{route("admin/company/list")}}',
        method: 'post',
        dataSrc: 'data',
        data: {
          '_token': CSRF_TOKEN,
        },
      },
      columns: [
        {
          data: "id",
          responsivePriority: 6
        }, //,visible:false
        {
          data: "company_name",
          responsivePriority: 4
        },
        // {
        //   data: "email",
        //   responsivePriority: 4
        // },
        {
          data: "title",
          responsivePriority: 4
        },
        {
          data: "amount",
          responsivePriority: 4
        },
        {
          data: "status",
          responsivePriority: 4
        },
        {
          data: "plan_id",
          responsivePriority: 4
        },
        {
          data: "expired_at",
          responsivePriority: 4
        },
        {
          data: "action",
          bSortable: false,
          responsivePriority: 2
        }
      ],
      responsive: true,
      serverSide: true,
      "order": [
        [0, "asc"]
      ]
    });
  });
</script>
@endpush