@extends('company.layouts.main')
@section('title')
Dashboard
@endsection
@section('content')
<?php 
$sessionUser = auth()->user(); 
$documentModel = new \App\Models\Document();
$statusName = isset($_GET['status']) ?$_GET['status'] :  @$status; 
$status = $documentModel->getStatusToStatusName(@$statusName);
?>
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
   a.divActive {
    border-radius: 5px;
    transition: background-color 0.3s ease;
    background-color : #435ebe;
    color:white !important;
    }
    a.textColor{
        color:#25396f;
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
        
        <div class="col-6 col-lg-4 col-md-6 mb-3">
            <div class="card h-100 mb-0">
                <a href="company/dashboard?status=Active" class="pjax {{$statusName == 'Active' ? 'divActive' : ''}}">
                    <div class="card-body px-4 py-4-5">
                        <div class="row">
                            <div class="col-sm-6 col-md-4 col-lg-6 col-xl-6 col-xxl-4 d-flex justify-content-start ">
                                <div class="stats-icon blue mb-2">
                                   <i class="fas fa-exclamation-circle"></i>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-8 col-lg-6 col-xl-6 col-xxl-7">
                                <h6 class="{{@$statusName == 'Active' ? 'text-white' : 'text-muted'}} font-semibold">Compliant</h6>
                                <h6 class="font-extrabold mb-0 {{@$statusName == 'Active' ? 'text-white' : 'textColor'}}">{{ @$activeUser }} <small>({{ @$compliancePercentage }}<span>%</span>)</small></h6>
                            </div>
                        </div>
                    </div>
                 </a>
            </div>
        </div>

        <div class="col-6 col-lg-4 col-md-6 mb-3">
            <div class="card h-100 mb-0">
                <a href="company/dashboard?status=Expiring Soon" class="pjax {{$statusName == 'Expiring Soon' ? 'divActive' : ''}}">
                    <div class="card-body px-4 py-4-5">
                        <div class="row">
                            <div class="col-sm-6 col-md-4 col-lg-6 col-xl-6 col-xxl-4 d-flex justify-content-start ">
                                <div class="stats-icon green bg-warning mb-2">
                                   <i class="fas fa-hourglass-end"></i>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-8 col-lg-6 col-xl-6 col-xxl-7">
                                <h6 class="{{@$statusName == 'Expiring Soon' ? 'text-white' : 'text-muted'}} font-semibold">Expiring Soon</h6>
                                <h6 class="font-extrabold mb-0 {{@$statusName == 'Expiring Soon' ? 'text-white' : 'textColor'}}">{{ @$deactiveUser }} <small>({{@$expiringPercentage }}<span>%</span>)</small></h6>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="col-6 col-lg-4 col-md-6 mb-3">
            <div class="card h-100 mb-0">
                <a href="company/dashboard?status=Expired" class="pjax {{$statusName == 'Expired' ? 'divActive' : ''}}">
                    <div class="card-body px-4 py-4-5">
                        <div class="row">
                            <div class="col-sm-6 col-md-4 col-lg-6 col-xl-6 col-xxl-4 d-flex justify-content-start ">
                                <div class="stats-icon red mb-2">
                                   <i class="fas fa-calendar-times"></i>
                                </div>
                            </div>
                            
                            <div class="col-sm-6 col-md-8 col-lg-6 col-xl-6 col-xxl-7">
                                <h6 class="{{@$statusName == 'Expired' ? 'text-white' : 'text-muted'}} font-semibold">Expired</h6>
                                <h6 class="font-extrabold mb-0 {{@$statusName == 'Expired' ? 'text-white' : 'textColor'}}">{{ @$expiredUser }} <small>({{ @$expiredPercentage }}<span>%</span>)</small></h6>
                            </div>
                        </div>
                    </div>
                 </a>   
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header d-flex align-items-center flex-wrap gap-2 justify-content-between pb-1">
                <div class="row w-100 mx-auto">
                    <div class="col-md-3 px-0">
                        <h5 class="card-title">Documents</h5>
                    </div>
                </div>
                
            </div>

            <div class="card-body dataTable-container">
                <table class="datatable-list-table table border-top" id="data-table1">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Contractor Name</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </section>

@endsection
@push('scripts')
<script>
window.statusExp = @json($status); 

function tableFilterExpire(){
     //   datatableObj.ajax.params().status = $('#status').val();
    //  tableDataExp.statusExp = $('#statusExp').val();
     tableDataExp.statusExp = window.statusExp;
     tableDataExp.startDateExp = $('#startDateExp').val();
     tableDataExp.company_name = $('#company_name').val();
     tableDataExp.documentTypeExp = $('#documentType1').val();
    datatableObj1.ajax.reload();
    }
    var tableDataExp = {
          '_token': CSRF_TOKEN,
        //   'statusExp': $('#statusExp').val(),
          'statusExp': window.statusExp,
          'documentTypeExp' : $('#documentType1').val(),
          'company_name' : $('company_name').val(),
          'startDateExp' : $('#startDateExp').val(),
        };
        
documentReady(function () {

    // Use a unique stateSave key per status
    var statusKey = window.statusExp || 'all';
    var dtStateKey = 'DataTables_data-table1_' + statusKey + '_' + window.location.pathname;
    // If the stored status is different, clear the state before DataTables is initialized
    var lastStatusKey = window.localStorage.getItem('datatable_last_status_key');
    if (lastStatusKey !== dtStateKey) {
        // Remove all DataTables state for this table
        for (var key in window.localStorage) {
            if (key.indexOf('DataTables_data-table1_') === 0) {
                window.localStorage.removeItem(key);
            }
        }
        window.localStorage.setItem('datatable_last_status_key', dtStateKey);
    }

    datatableObj1 = $('#data-table1').DataTable({
         pageLength: 25, 
         lengthMenu: [25, 50, 100],
         stateSave :true,
         stateSaveParams: function(settings, data) {
            // Save the current status in the state
            data.statusKey = statusKey;
         },
         stateLoadParams: function(settings, data) {
            // Only load state if status matches
            if (data.statusKey !== statusKey) {
                return false;
            }
         },
         stateSaveCallback: function(settings, data) {
            // Save state with our custom key
            window.localStorage.setItem(dtStateKey, JSON.stringify(data));
         },
         stateLoadCallback: function(settings) {
            // Load state with our custom key
            var data = window.localStorage.getItem(dtStateKey);
            return data ? JSON.parse(data) : null;
         },
        ajax: {
            url: '{{ route("company/contractor/documentlist-bystatus") }}',
            method: 'post',
            dataSrc: 'data',
            data: function (d) {
                return $.extend({}, d, tableDataExp);
            }
        },
        columns: [
            { data: "id", responsivePriority: 4 },
            { data: "first_name", responsivePriority: 4 },
            // { data: "company_id", responsivePriority: 4 },
            // { data: "type", responsivePriority: 2 },
            // { data: "expired_at", responsivePriority: 4 },
            { data: "status", responsivePriority: 3 },
            { data: "action", responsivePriority: 3 }
        ],
        responsive: true,
        serverSide: true,
        order: [[0, "desc"]]
    });

});

</script>
@endpush