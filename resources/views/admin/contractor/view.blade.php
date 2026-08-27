@extends('admin.layouts.main')
@section('title')
Contractor View
@endsection
@section('content')

<?php $sessionUser = auth()->user(); 
    $permission = explode(',', $sessionUser->permission);
?>

<div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Contractor</h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin/dashboard') }}" class="pjax">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin/contractor') }}" class="pjax">Contractor</a></li>

                        <li class="breadcrumb-item active" aria-current="page">Contractor View</li>
                    </ol>
                </nav>
            </div>
        </div>
</div>

<!-- Content -->
<div class="row">
  <div class="col-12 order-1 order-md-0">
    <div class="card mb-4">
      <div class="card-body d-flex flex-column flex-sm-row gap-0 gap-sm-3 gap-lg-2 gap-xl-4 custCardbody">
        
        <!-- Avatar Section -->
        <div class="user-avatar-section">
          <div class="d-flex align-items-center flex-column">
            <img class="img-fluid rounded mb-3 pt-1 mt-4" src="{{ $general->getFileUrl($model->image,'profile') }}" height="100" width="100" alt="User avatar" />
            <div class="user-info text-center">
              <h4 class="mb-2">{{ $model->first_name.' '.$model->last_name }}</h4>
              <!--<span class="badge bg-secondary">Contractor</span>-->
            </div>
          </div>
        </div>

        <!-- Details Section -->
        <div class="flex-grow-1">
          <p class="mt-0 small text-uppercase text-muted">Details</p>

          <!-- FLEX container instead of Bootstrap row -->
          <div class="d-flex flex-column flex-md-row gap-md-5 gap-0">
            
            <!-- First List -->
            <div>
              <ul class="list-unstyled">
                <li class="mb-2">
                  <span class="fw-semibold me-1">Username:</span>
                  <span>{{ $model->first_name.' '.$model->last_name }}</span>
                </li>
                <li class="mb-2 pt-1">
                  <span class="fw-semibold me-1">Business Name:</span>
                  <span>{{ $model->business_name }}</span>
                </li>
                <li class="mb-2 pt-1">
                  <span class="fw-semibold me-1">Company Name:</span>
                  <span>{{ (new \App\Models\User())->getCompanyName($model->company_id) }}</span>
                </li>
                <li class="mb-2 pt-1">
                  <span class="fw-semibold me-1">Email:</span>
                  <span>{{ $model->email }}</span>
                </li>
                <li class="mb-2 pt-1">
                  <span class="fw-semibold me-1">Status:</span>
                  @if($model->status == 0)
                    <span class="badge bg-danger">Inactive</span>
                    <span class="badge bg-secondary">Contractor</span>
                  @else
                    <span class="badge bg-success">Active</span>
                    <span class="badge bg-secondary">Contractor</span>
                  @endif
                </li>
                
              </ul>
            </div>
            <!-- Second List -->
            <div>
              <ul class="list-unstyled">
                <li class="mb-2 ">
                  <span class="fw-semibold me-1">Created at:</span>
                  <span>{{ $model->created_at->format('Y-m-d h:i A') }}</span>
                 </li>
                <li class="mb-2 pt-1">
                  <span class="fw-semibold me-1">Updated at:</span>
                  <span>{{ $model->updated_at->format('Y-m-d h:i A') }}</span>

                </li>
                <li class="mb-2 pt-1">
                  <span class="fw-semibold me-1">Time Zone:</span>
                  <span>{{ $model->timezone }}</span>
                </li>
                <li class="mb-2 pt-1">
                  <span class="fw-semibold me-1">Register IP:</span>
                  <span>{{ $model->registered_ip }}</span>
                </li>
                
              </ul>
            </div>
          </div>
            
          <!-- Buttons -->
          <div class="d-flex mt-3 btnForRes">
            @if($sessionUser->hasPermission('admin/contractor/update')) 
            <a href="admin/contractor/update?id={{ $_GET['id'] }}" class="btn btn-primary me-3 pjax">Edit</a>
            @endif
            
            @if($sessionUser->hasPermission('admin/contractor/delete'))
            <button onclick="app.confirmStatusAction(this);" data-action="admin/contractor/change_status?id={{ $_GET['id'] }}" class="btn @if($model->status ==0) btn-success @else btn-danger @endif me-3">@if($model->status == 0) Set as active @else Set as inactive @endif</button>
            @endif
            
            @if(in_array('admin/login-as-contractor', $permission))
                <a href="{{ route('admin/login-as-contractor', ['id' => $_GET['id']]) }}" class="btn btn-primary me-3">Login as Contractor</a>
            @endif
                
             @if(in_array('admin/contractor/document/send-remindermail', $permission))
            <button class="btn btn-secondary" onclick="app.showModalView('{{ route('admin/contractor/document/send-remindermail', ['id' => $_GET['id'] ]) }}')">Send Mail</button>
            @endif
                
          </div>
        </div>
        
      </div>
    </div>
  </div>
</div>

@if($sessionUser->hasPermission('admin/contractor/document'))
<section class="section">
        <div class="card">
            <div class="card-header justify-content-between d-flex align-items-center flex-wrap gap-2 pb-2">
                <h5 class="card-title">
                Documents
                </h5>
                <!--<a href="{{route('admin/document/create',['id'=>$model->id])}}" class="btn btn-primary d-sm-inline-block d-none pjax" style="float: inline-end;">Create</a>-->
                   <div class="d-flex flex-wrap flex-wrap gap-2 center-md">
                 
                        <div class="form-group">    
                            <label class="body" for="startDateExp">Select Date</label>
                              <div class="form-group mb-0 position-relative has-icon-right">
                                 <input type="date" class="form-control flatpickr-range" id="startDateExp" onchange="tableFilterList()" name="startDateExp"  value="">
                                   <div class="form-control-icon">
                                     <i class="bi bi-calendar-event"></i>
                                   </div>
                             </div>
                          </div>
                   
                        <div class="form-group">
                            <label class="body">Document Type</label>
                            <select class="form-select"
                               name="documentType" onchange="tableFilterList();" id="documentType">
                                <option value="">All</option>
                                @foreach($documentTypeData as $docType)
                                <option value="{{$docType->id}}">{{$docType->name}}</option>
                                @endforeach
                            </select>
                        </div>
                  
                        <div class="form-group">
                            <label class="body">Status</label>
                            <select class="form-select"
                               name="docStatus" onchange="tableFilterList();" id="docStatus">
                                <option value="">All</option>
                                <option value="1">Pending</option>
                                <option value="5">Active</option>
                                <option value="2">Expiring Soon</option>
                                <option value="3">Expired</option>
                                <option value="4">Inactive</option>
                            </select>
                      
                    </div>
                    
                </div>
                  </div>
            
            <div class="card-body dataTable-container">
                    <table class="datatable-list-table table border-top" id="data-table">
                        <thead>
                            <tr>
                            <th>Id</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Upload Date</th>
                            <th>Expiration Date</th>
                             @if($sessionUser->hasPermission('admin/document/view') || $sessionUser->hasPermission('admin/document/change_status') || $sessionUser->hasPermission('admin/contractor/document/send-remindermail'))
                            <th>Actions</th>
                            @endif
                            </tr>
                        </thead>
                    </table>
            </div>
        </div>
</section>
@endif


@if($userAccountModel)
<section class="section">
    <div class="row">
        <div class="col-lg-6">
            <div class="card equal-cards">
                <div class="card-header d-flex flex-wrap gap-2 align-items-center justify-content-between">
                    <h5 class="card-title">Payment Details </h5>
                    @if($extension == 'pdf')
                    <div class="">
                        <button class="btn btn-primary" onclick="app.showModalView('{{ route('admin/contractor/document-file/pdf-preview',['fileName'=> $userAccountModel->file_name]) }}')">Preview</button>
                        <a  href="{{ route('admin/document/show', ['fileName' => $userAccountModel->file_name]) }}" class="btn btn-success" download>Download</a>
                    </div>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <table class="table">
                                <tr>
                                    <th>Bank Name : </th>
                                    <td>{{$userAccountModel->bank_name}}</td>
                                </tr>
                                <tr>
                                    <th>Account Number : </th>
                                    <td>{{$userAccountModel->account_number}}</td>
                                </tr>
                                <tr>
                                    <th>Routing Number : </th>
                                    <td>{{$userAccountModel->routing_number}}</td>
                                </tr>
                            </table>
                        </div>
                         @if($extension !== 'pdf')
                          <div class="col-12 mt-2 mt-md-0 mb-md-0 mb-2">
                               <div class="view-info-img">
                                   <img class="w-100 active landscap-img" src=" {{ route('admin/document/show', ['fileName' => $userAccountModel->file_name]) }}" data-bs-target="#Gallerycarousel" data-bs-slide-to="0">
                               </div>
                          </div>
                          @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">    
@else

        <div class="col-lg-12">
        @endif

                <div class="card equal-cards">
                    <div class="card-header">
                        <h5 class="card-title">Compliance Status</h5>
                    </div>
                  
                    <div class="card-body">
                     <div id="radialGradient"></div>
                    <!-- @if(@$uploadRequiredDocument !== @$totalRequiredDocument)-->
                    <!--        <p class="alert alert-light-warning color-warning d-flex align-items-center gap-2"><i class="bi bi-exclamation-circle d-flex"></i>-->
                    <!--        <b class="text-nowrap"> Action Required: </b> Please upload your missing document and renew your expiring Business License.</p>-->
                    <!--@endif  -->
                    </div>
                </div>
            </div>

</section>


<section class="section">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title">
                Documents Activity
                </h5>
            </div>
            <div class="card-body dataTable-container">
                <table class="datatable-list-table table border-top" id="data-table1">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Description</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
</section>

@endsection

@push('scripts')
<script>

    
function tableFilterList(){
    tableData.documentType = $('#documentType').val();
    tableData.startDateExp = $('#startDateExp').val();
    tableData.docStatus = $('#docStatus').val();
    datatableObj.ajax.reload();
    }
    var tableData = {
          '_token': CSRF_TOKEN,
          'documentType' : $('#documentType').val(),
          'startDateExp' : $('#startDateExp').val(),
          'docStatus' : $('#docStatus').val()
        };
        
documentReady(function() {
    const hasUpdatePermission = {!! json_encode(
        $sessionUser->hasPermission('admin/document/view') ||
        $sessionUser->hasPermission('admin/document/change_status') ||
        $sessionUser->hasPermission('admin/contractor/document/send-remindermail')
    ) !!};
    
    datatableObj = $('#data-table').DataTable({
         pageLength: 25, 
         lengthMenu: [25, 50, 100],
         stateSave :true,
      ajax: {
        url: "{{route('admin/contractor/document',['id'=>$model->id])}}",
        method: 'post',
        dataSrc: 'data',
       data: function(d) {
                return $.extend({}, d, tableData);
            }
      },
    
      columns: [{
          data: "id",
          responsivePriority: 4
        }, //,visible:false
        {
          data: "type",
          responsivePriority:2
        }, //,visible:false
   
        {
          data: "status",
          responsivePriority: 3
        },
        {
          data: "created_at",
          responsivePriority: 4
        },
        {
          data: "expired_at",
          responsivePriority: 4
        },
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
    
     var compliancePercentage = <?php echo json_encode($compliancePercentage); ?>;
    //var compliancePercentage = {{ $compliancePercentage }};
    renderChart(compliancePercentage);
});



documentReady(function() {
    datatableObj1 = $('#data-table1').DataTable({
         pageLength: 25,    
         lengthMenu: [25, 50, 100],
         stateSave :true,
      ajax: {
        url: '{{route("admin/contractor/documents-activity" , ['id'=>@$model->id])}}',
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
          data: "admin_description",
          responsivePriority: 4
        }, //,visible:false
         {
          data: "created_at",
          responsivePriority: 4
        }, //,visible:false
      ],
      responsive: true,
      serverSide: true,
      "order": [
        [0, "desc"]
      ]
    });
});


function renderChart(compliancePercentage) {
    
    // Ensure the chart container exists
    const chartContainer = document.querySelector("#radialGradient");
    if (!chartContainer) {
        console.error("Chart container not found!");
        return;
    }

    var radialGradientOptions = {
        series: [parseFloat(compliancePercentage)],
        chart: {
            height: 350,
            type: "radialBar",
            toolbar: { show: true },
        },
        plotOptions: {
            radialBar: {
                startAngle: -135,
                endAngle: 225,
                hollow: {
                    margin: 0,
                    size: "70%",
                    background: "#fff",
                    dropShadow: { enabled: true, top: 3, left: 0, blur: 4, opacity: 0.24 },
                },
                track: {
                    background: "#fff",
                    strokeWidth: "67%",
                    margin: 0,
                    dropShadow: { enabled: true, top: -3, left: 0, blur: 4, opacity: 0.35 },
                },
                dataLabels: {
                    show: true,
                    name: {
                        offsetY: -10,
                        show: true,
                        color: "#888",
                        fontSize: "17px",
                    },
                    value: {
                       formatter: function () {
                            return Math.round(compliancePercentage) + "%";
                        },
                        color: "#111",
                        fontSize: "36px",
                        show: true,
                    },
                },
            },
        },
        fill: {
            type: "gradient",
            gradient: {
                shade: "dark",
                type: "horizontal",
                shadeIntensity: 0.5,
                gradientToColors: ["#ABE5A1"],
                inverseColors: true,
                opacityFrom: 1,
                opacityTo: 1,
                stops: [0, 100],
            },
        },
        stroke: { lineCap: "round" },
        labels: ["Percent"],
    };

    // Render the chart
    var radialGradient = new ApexCharts(chartContainer, radialGradientOptions);
    radialGradient.render();
}
</script>
@endpush