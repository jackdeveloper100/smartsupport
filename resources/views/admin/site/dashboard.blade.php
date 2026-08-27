@extends('admin.layouts.main')
@section('title')
Dashboard
@endsection
@section('content')
<?php $sessionUser = auth()->user(); ?>
<style>
  .more {
    color: rgba(255, 255, 255, .8);
    display: block;
    padding: 3px 0;
    position: relative;
    text-align: center;
    text-decoration: none;
    z-index: 10;
  }
  </style>



<div class="page-heading">
    <h3>Dashboard</h3>
</div> 

   <!-- Show success message if it exists in localStorage -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Check if there's a success message in localStorage
            const successMessage = localStorage.getItem('success_message');
            if (successMessage) {
                app.showSweetAlertToast(successMessage, 'success');
                // Clear the success message after displaying it
                localStorage.removeItem('success_message');
            }
        });
    </script>



@if($sessionUser->hasPermission('admin_dashboard'))
    <div class="row">
        <div class="col">
            <div class="refresh-btn text-end pb-3">
                <button class="btn btn-primary" onclick="pjax.loadPage(window.location.href)">
                    Refresh <i class="bi bi-arrow-clockwise"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="row dashboard-head">
        <div class="col-6 col-lg-3 col-md-6 mb-3">
            <div class="card h-100 mb-0">
                <a href="admin/contractors" class="pjax">
                    <div class="card-body px-4 py-4-5">
                        <div class="row">
                            <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-4 d-flex justify-content-start ">
                                <div class="stats-icon purple mb-2">
                                    <i class="iconly-boldShow"></i>
                                </div>
                            </div>
                            <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                <h6 class="text-muted font-semibold">Total Contractors </h6>
                                <h6 class="font-extrabold mb-0">{{ $totalUser }} </h6>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="col-6 col-lg-3 col-md-6 mb-3">
            <div class="card h-100 mb-0">
                <a href="admin/contractor/documents?status=Active" class="pjax">
                    <div class="card-body px-4 py-4-5">
                        <div class="row">
                            <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-4 d-flex justify-content-start ">
                                <div class="stats-icon blue mb-2">
                                   <i class="fas fa-exclamation-circle"></i>
                                </div>
                            </div>
                            <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                    <h6 class="text-muted font-semibold">Compliant</h6>
                                    <h6 class="font-extrabold mb-0">{{ $activeUser }} <small>({{ $compliancePercentage }}<span>%</span>)</small></h6>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="col-6 col-lg-3 col-md-6 mb-3">
            <div class="card h-100 mb-0">
                <a href="admin/contractor/documents?status=Expiring Soon" class="pjax">
                    <div class="card-body px-4 py-4-5">
                        <div class="row">
                            <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-4 d-flex justify-content-start ">
                                <div class="stats-icon green bg-warning mb-2">
                                   <i class="fas fa-hourglass-end"></i>
                                </div>
                            </div>
                            <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                <h6 class="text-muted font-semibold">Expiring Soon</h6>
                                <h6 class="font-extrabold mb-0">{{ $deactiveUser }} <small>({{$expiringPercentage }}<span>%</span>)</small></h6>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="col-6 col-lg-3 col-md-6 mb-3">
            <div class="card h-100 mb-0">
                <a href="admin/contractor/documents?status=Expired" class="pjax">
                    <div class="card-body px-4 py-4-5">
                        <div class="row">
                            <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-4 d-flex justify-content-start ">
                                <div class="stats-icon red mb-2">
                                   <i class="fas fa-calendar-times"></i>
                                </div>
                            </div>
                            <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                <h6 class="text-muted font-semibold">Expired</h6>
                                <h6 class="font-extrabold mb-0">{{ $expiredUser }} <small>({{$expiredPercentage }}<span>%</span>)</small></h6>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
    
    <div class="row dashboard-head">
    <div class="col-6 col-lg-3 col-md-6 mb-3">
        <div class="card h-100 mb-0">
            <div class="card-body px-4 py-4-5">
                <div class="row">
                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-4 d-flex justify-content-start">
                          <div class="stats-icon purple mb-2">
                                <i class="fas fa-building"></i>
                            </div>
                    </div>
                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                        <h6 class="text-muted font-semibold">Total Company</h6>
                        <h6 class="font-extrabold mb-0">{{ $totalCompany }}</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Company -->
    <div class="col-6 col-lg-3 col-md-6 mb-3">
        <div class="card h-100 mb-0">
            <div class="card-body px-4 py-4-5">
                <div class="row">
                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-4 d-flex justify-content-start">
                        <div class="stats-icon bg-success text-white mb-2">
                            <i class="fas fa-check-circle"></i> <!-- New Icon -->
                        </div>
                    </div>
                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                        <h6 class="text-muted font-semibold">Active Company</h6>
                        <h6 class="font-extrabold mb-0">{{ $activeCompany }}</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inactive Company -->
    <div class="col-6 col-lg-3 col-md-6 mb-3">
        <div class="card h-100 mb-0">
            <div class="card-body px-4 py-4-5">
                <div class="row">
                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-4 d-flex justify-content-start">
                        <div class="stats-icon bg-warning text-white mb-2">
                            <i class="fas fa-user-slash"></i> <!-- New Icon -->
                        </div>
                    </div>
                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                        <h6 class="text-muted font-semibold">Inactive Company</h6>
                        <h6 class="font-extrabold mb-0">{{ $inactiveCompany }}</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Received Payment -->
    <div class="col-6 col-lg-3 col-md-6 mb-3">
        <div class="card h-100 mb-0">
            <div class="card-body px-4 py-4-5">
                <div class="row">
                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-4 d-flex justify-content-start">
                        <div class="stats-icon bg-info text-white mb-2">
                            <i class="fas fa-hand-holding-usd"></i> <!-- New Icon -->
                        </div>
                    </div>
                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                        <h6 class="text-muted font-semibold">Total Received Payment</h6>
                        <h6 class="font-extrabold mb-0"><span id="totalReceivedPayment">Loading...</span></h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


    <section class="section">
        <div class="card">
            <div class="card-header d-flex align-items-center flex-wrap gap-2 justify-content-between pb-1">
                <div class="row w-100 mx-auto">
                    <div class="col-md-3 px-0">
                        <h5 class="card-title">Upcoming Expirations Panel</h5>
                    </div>
                    <div class="col-md-9 px-0">
                        <div class="d-flex align-items-center flex-wrap gap-2 justify-content-end calendar-row">
                            <div class="form-group">
                                <label class="body" for="startDateExp">Select Date</label>
                                <div class="form-group mb-0 position-relative has-icon-right">
                                    <input type="date" class="form-control flatpickr-range" id="startDateExp" onchange="tableFilterExpire()" name="startDateExp"  value="">
                                    <div class="form-control-icon">
                                        <i class="bi bi-calendar-event"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="body">Document Type</label>
                                <select class="form-select" name="documentType" onchange="tableFilterExpire();" id="documentType1">
                                    <option value="">All</option>
                                    @foreach($documentTypeData as $docType)
                                    <option value="{{$docType->id}}">{{$docType->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="body">Status</label>
                                <select class="form-select" name="status" onchange="tableFilterExpire();" id="statusExp">
                                    <option value="">All</option>
                                    <option value="2">Expiring Soon</option>
                                    <option value="3">Expired</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="body">Company</label>
                                <select class="form-select" name="status" onchange="tableFilterExpire();" id="company_name">
                                    <option value="">All</option>
                                    @foreach($company as $company)
                                    <option value="{{$company->id}}">{{$company->company_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body dataTable-container">
                <table class="datatable-list-table table border-top" id="data-table1">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Contractor Name</th>
                            <th>Company Name</th>
                            <th>Document Name</th>
                            <th>Expiration Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </section>
@else
    <p>You Do Not Have Permission To View This Page.</p>
@endif
@endsection

@push('scripts')
<script>


function tableFilterExpire(){
     //   datatableObj.ajax.params().status = $('#status').val();
     tableDataExp.statusExp = $('#statusExp').val();
     tableDataExp.startDateExp = $('#startDateExp').val();
     tableDataExp.company_name = $('#company_name').val();
     tableDataExp.documentTypeExp = $('#documentType1').val();
    datatableObj1.ajax.reload();
    }
    var tableDataExp = {
          '_token': CSRF_TOKEN,
          'statusExp': $('#statusExp').val(),
          'documentTypeExp' : $('#documentType1').val(),
          'company_name' : $('company_name').val(),
          'startDateExp' : $('#startDateExp').val(),
        };
        
documentReady(function () {

    datatableObj1 = $('#data-table1').DataTable({
         pageLength: 25, 
         lengthMenu: [25, 50, 100],
         stateSave :true,
        ajax: {
            url: '{{ route("admin/dashboard/expirationalList") }}',
            method: 'post',
            dataSrc: 'data',
            data: function (d) {
                return $.extend({}, d, tableDataExp);
            }
        },
        columns: [
            { data: "id", responsivePriority: 4 },
            { data: "first_name", responsivePriority: 4 },
            { data: "company_id", responsivePriority: 4 },
            { data: "type", responsivePriority: 2 },
            { data: "expired_at", responsivePriority: 4 },
            { data: "status", responsivePriority: 3 }
        ],
        responsive: true,
        serverSide: true,
        order: [[0, "desc"]]
    });

    fetchTotalReceivedPayment();
});

function fetchTotalReceivedPayment() {
    $.ajax({
        url: '{{ route("admin/dashboard/totalReceivedPayment") }}',
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        success: function (response) {
            if (response.success) {
                $('#totalReceivedPayment').text('$' + response.amount.toFixed(2));
            } else {
                $('#totalReceivedPayment').text("Error");
            }
        },
        error: function (xhr, status, error) {
            console.error("AJAX error:", error);
            $('#totalReceivedPayment').text("Error");
        }
    });
}
</script>
@endpush