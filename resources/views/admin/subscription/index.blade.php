@extends('admin.layouts.main')
@section('title')
Subscriptions
@endsection
@section('content')

<?php $sessionUser = auth()->user(); ?>

<!-- Content -->

<div class="page-title">
    <div class="row">
        <div class="col-12 col-md-4 order-md-1 order-last">
            <h3>Subscriptions</h3>
        </div>
        <div class="col-12 col-md-8 order-md-2 order-first">
            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-sm-end">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="admin/dashboard" class="pjax">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Subscriptions</li>
                </ol>
            </nav>
        </div>
    </div>
</div>
<!-- Invoice List Table -->
 <div class="section">
  <div class="card">
    <div class="card-header ">
      <h4 class="card-title">Subscriptions</h4>
    </div>
    <div class="card-body">
      <div class="card-datatable table">
        <table class="datatables table" id="data-table">
          <thead>
            <tr>
              <th>Id</th>
              <th>Company Name</th>
              <th>Plan Name</th>
              <th>Price</th>
              <th>Status</th>
              <th>Created Date</th>
              <th>Updated Date</th>
              <th>Expired Date</th>
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

    datatableObj = $('#data-table').DataTable({
         pageLength: 25, 
         lengthMenu: [25, 50, 100],
      stateSave: true,
      ajax: {
        url: '{{route("admin/company/subscription-list")}}',
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
          data: "user_id",
          responsivePriority: 6
        }, //,visible:false
        {
          data: "stripe_subscription_id",
          responsivePriority: 6
        }, //,visible:false
        {
          data: "amount",
          responsivePriority: 6
        }, //,visible:false
        {
          data: "status",
          responsivePriority: 6
        }, //,visible:false
        {
          data: "created_at",
          responsivePriority: 6
        }, //,visible:false
        {
          data: "updated_at",
          responsivePriority: 6
        }, //,visible:false
        {
          data: "expired_at",
          responsivePriority: 6
        }, //,visible:false
           
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