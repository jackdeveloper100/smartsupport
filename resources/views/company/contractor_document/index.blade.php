@extends('company.layouts.main')
@section('title')
Documents
@endsection
@section('content')
<?php 
$sessionUser = auth()->user(); 
$documentModel = new \App\Models\Document();
$statusName = isset($_GET['status']) ?$_GET['status'] :  'Pending'; 
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

<div class="page-title">
    <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
            <h3>Documents</h3>
        </div>
        <div class="col-12 col-md-6 order-md-2 order-first">
            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-sm-end">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="company/dashboard" class="pjax">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Documents</li>
                </ol>
            </nav>
        </div>
    </div>
</div>
<div class="row dashboard-head">
        
        <div class="col-6 col-lg-4 col-md-6 mb-3">
            <div class="card h-100 mb-0">
                <a href="company/contractor/documents?status=Active" class="pjax {{$statusName == 'Active' ? 'divActive' : ''}}">
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
                <a href="company/contractor/documents?status=Expiring Soon" class="pjax {{$statusName == 'Expiring Soon' ? 'divActive' : ''}}">
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
                <a href="company/contractor/documents?status=Expired" class="pjax {{$statusName == 'Expired' ? 'divActive' : ''}}">
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
                            <!--<th>Company Name</th>-->
                            <!--<th>Document Name</th>-->
                            <!--<th>Expiration Date</th>-->
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

    datatableObj1 = $('#data-table1').DataTable({
         pageLength: 25, 
         lengthMenu: [25, 50, 100],
         stateSave :true,
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