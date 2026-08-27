@extends('layouts.main')
@section('title')
Contractor Documents
@endsection
@section('content')

<?php
$userId = auth()->id();
$sessionUser = auth()->user();
?>

<style>
    .preview{
        height : auto;
    }
</style>

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

<div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>My Documents</h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-sm-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="pjax">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Document</li>
                    </ol>
                </nav>
            </div>
        </div>
</div>

<div class="section">
    <div class="row">
        <div class="col-12">
            <h6 class="card-text">View and Manage Your Compliance Documents</h6>
            <div class="card">
                <div class="card-header pb-1">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h4 class="card-title mb-0">Required Documents</h4>
                        <h6 class="card-text mb-0">{{$uploadedDocumet}} of {{$totalDocument}} Documents Uploaded</h6>
                    </div>
                </div>
                 <div class="card-content">
                        <div class="card-body pt-1">
                            <!-- Table with outer spacing -->
                            <div class="table-responsive">
                                <table class="table table-lg">
                                    <thead>
                                        <tr>
                                            <th>Document</th>
                                            <th>Status</th>
                                            <th>Uploaded</th>
                                            <th>Expiration</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($documents as $document)
                                        <tr>
                                            <td>
                                                <i class="bi bi-file-earmark"></i> {{$document['name']}}
                                                @if($document['document_reqiure'] == 0)
                                                <p>Optional</p>
                                                @else
                                                <p>Required</p>
                                                @endif
                                            </td>

                                            <td>{!!$document['status'] !!}</td>
                                            <td>{{$document['uploaded_at']}}</td>
                                         
                                            <td>{{$document['expired_at']}}</td>
                                            @if(strpos($document['status'], 'Missing') !== false) 
                                            <td>
                                                @if(session('came_from_company') || session('came_from_admin') || $isCompanyUnlimitedContractor == 1)
                                                    <a href="javascript:void(0)" class="act-btns tool-btn me-2"  onclick="app.showModalView('{{ route('contractor/document/create', ['name' => $document['name'], 'type' => $document['type']]) }}')" >
                                                    <i class="bi bi-plus-circle-fill"></i><span class="tooltip-text">Upload</span></a>
                                                @elseif($companyPlanId != "1" && $companyPlanId != null)
                                                    <a href="javascript:void(0)" class="act-btns tool-btn me-2"  onclick="app.showModalView('{{ route('contractor/document/create', ['name' => $document['name'], 'type' => $document['type']]) }}')" >
                                                    <i class="bi bi-plus-circle-fill"></i><span class="tooltip-text">Upload</span></a>
                                                @endif
                                            </td>
                                            @else   
                                            <td>
                                                @if(session('came_from_company') || session('came_from_admin') || $isCompanyUnlimitedContractor == 1)
                                                    <a href="javascript:void(1)"  class="act-btns tool-btn me-2"  onclick="app.showModalView('{{ route('contractor/document/update',  ['name' => $document['name'], 'type' => $document['type']]) }}')" ><i class="bi bi-pencil-square"></i>
                                                    <span class="tooltip-text">Replace</span></a>
                                                @elseif($companyPlanId != "1" && $companyPlanId != null)
                                                    <a href="javascript:void(1)"  class="act-btns tool-btn me-2"  onclick="app.showModalView('{{ route('contractor/document/update',  ['name' => $document['name'], 'type' => $document['type']]) }}')" ><i class="bi bi-pencil-square"></i>
                                                    <span class="tooltip-text">Replace</span></a>
                                                @endif
                                                <a href="{{ route('contractor/document/view',  ['name' => $document['name'], 'type' => $document['type']]) }}" class="pjax act-btns tool-btn me-2" ><i class="bi bi-eye-fill"></i><span class="tooltip-text">View</span></a>
                                            </td>
                                            @endif
                                        </tr>
                                        @endforeach
                                    </tbody>
                            </table>
                        </div>
            </div>
        </div>
    </div>
</div>

<div class="section">
    <div class="row">
        <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Payment Details</h5>
            </div>
            <div class="card-body">
                <form class="ajax-form1" method="post" action="{{ route('contractors/document/save-account') }}" enctype="multipart/form-data" id="ajax-form">
                    @csrf
                    <input type = 'hidden' name="user_id" value = "{{$userId}}">
                    <input type = 'hidden' name ="id" value="{{@$userAccountModel->id}}">
                    <div class = "row">
                        
                        <div class="col-md-6">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="body" for="rejected_reason">Bank Name <span class="star">*</span></label>
                                    <input type="text" class="form-control" id="bank_name" name="bank_name" value="{{@$userAccountModel->bank_name}}" placeholder="Enter Bank Name" />
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="body" for="rejected_reason">Account Number <span class="star">*</span></label>
                                    <input type="text" class="form-control" id="acc_number" name="account_number" value="{{@$userAccountModel->account_number}}" placeholder="Enter Account Number" />
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="body" for="rejected_reason">Routing Number <span class="star">*</span></label>
                                    <input type="text" class="form-control" id="routing_number" name="routing_number" value="{{@$userAccountModel->routing_number}}" placeholder="Enter Routing Number" />
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <?php if (!empty($userAccountModel->file_name)) { ?>
                                <div class="img-preview mb-4 my-4 ">
                                    @if(@$extension == 'pdf')
                                    <div class="d-flex justify-content-end" id="pdf-buttons" style="display:block;">
                                        <button class="btn btn-primary preview img-fluid preview-image payment-upload-img me-2" type="button">Preview</button>
                                        <a href="{{ route('document/show', ['fileName' => $userAccountModel->file_name]) }}" class="btn btn-success" download>Download</a>
                                    </div>
                                    @else
                                    <img src="{{ route('document/show', ['fileName' => $userAccountModel->file_name]) }}" class="img-fluid preview-image payment-upload-img" id="image">
                                    @endif
                                </div>
                            <?php } else { ?>
                                <div class="img-preview mb-4">
                                    <img src="{{ $general->getNoFile() }}" class="img-fluid payment-upload-img payment-upload-img" id="image">
                                </div>
                            <?php } ?>
                            <div class="form-group">
                                <label class="body">File <span class="star">*</span></label>
                                <input type="file" class="form-control" accept="image/*,application/pdf" name="image" id="fileInput" onchange="handleFileChange(event)">
                            </div>
                        </div>

                            
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </div>
                    </div>


                    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="imageModalLabel">
                                @if(@$extension == 'pdf')
                                File Preview
                                @else
                                Image Preview
                                @endif
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        
                        <div class="modal-body text-center">
                            @if(@$extension == 'pdf') 
                            <iframe src="{{ route('document/show', ['fileName' => $userAccountModel->file_name]) }} " frameborder="0" height="550px" width="100%"></iframe>
                            @else
                            <img src="" id="modalImage" class="img-fluid popup-img"/>
                            @endif
                        </div>
                        </div>
                    </div>
                    </div>
            </div>
        </div>
    </div>
</div>

<div class="section">
    <div class="row">
        <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Compliance Status</h5>
                    </div>
                  
                    <div class="card-body">
                     <div id="radialGradient"></div>
                            @if((@$totalRequiredDocument !== @$uploadRequiredDocument) || !empty($expiredDocument) )
                                <p class="alert alert-light-warning color-warning d-flex align-items-center flex-wrap gap-2"><i class="bi bi-exclamation-circle d-flex"></i>
                                <b class="text-nowrap"> Action Required: </b> {{ $actionMessage }} </p>
                            @endif
                    </div>
                </div>
            </div>
    </div>
    
</div>

@if($sessionUser)
<section class="section">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title">
                Documents History
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
@endif

@endsection
@push('scripts')
<script type="text/javascript">
let complianceChart = null;

documentReady(function() {
    const complianceRaw = @json($compliancePercentage ?? 0);
    renderChart(complianceRaw);
    
    $('.ajax-form1').validate({
        submitHandler: function(form) {
            app.ajaxFileForm(form);
        }
    })
    
        $(".preview-image").on("click", function() {
        var imgSrc = $(this).attr("src");
        $("#modalImage").attr("src", imgSrc);
        $('#imageModal').modal('show');
    });

    $('.ajax-form').validate({
        submitHandler: function(form) {
            app.ajaxFileForm(form);
        }
    });

    let limit = 10;  
    $('#load-more-btn').on('click', function() {
        let user_id = "{{ $userId }}"; 
        $.ajax({
            url: 'contractor/notifications/load-more', 
            method: 'GET',
            data: {
                user_id: user_id,
                limit: limit,
            },
            success: function(response) {
                if (response.notifications.length > 0) {
                    response.notifications.forEach(function(notification) {
                        let notificationRow = `
                            <tr>
                                <td class="px-0 p-sm-3">
                                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-normal align-items-sm-center flex-wrap gap-sm-5 gap-1">
                                        <div class="d-flex gap-2 align-items-center">
                                            ${notification.badge} <!-- Assuming the badge is returned in response -->
                                            ${notification.description}
                                        </div>
                                        <p class="mb-0 text-end">${notification.created_at}</p>
                                    </div>
                                </td>
                            </tr>
                        `;
                        $('#notification-table-body').append(notificationRow);
                    });
                    limit += 10;
                    if (response.notifications.length < 10) {
                        $('#load-more-btn').hide();
                    }
                } else {
                    // In case of an empty response, hide the button immediately
                    $('#load-more-btn').hide();
                }
            },
            error: function() {
                alert('Error loading notifications.');
            }
        });
    });

    // Initialize DataTable
    var datatableObj1 = $('#data-table1').DataTable({
         pageLength: 25, 
         lengthMenu: [25, 50, 100],
         stateSave :true,
        ajax: {
            url: '{{ route("contractor/documents-history") }}',
            method: 'POST',
            dataSrc: 'data',
            data: {
                '_token': CSRF_TOKEN
            }
        },
        columns: [
            { data: "id", responsivePriority: 4 },
            { data: "description", responsivePriority: 4 },
            { data: "created_at", responsivePriority: 4 },
        ],
        responsive: true,
        serverSide: true,
        order: [[0, "desc"]]
    });
    
    
     $('#pdf-buttons').hide();

    // Handle file selection
    $('#fileInput').on('change', function(event) {
        const file = event.target.files[0];
        const imageElement = $('#image');
        const pdfButtons = $('#pdf-buttons');
        
        if (file) {
            // Check if the file is a PDF
            if (file.type === 'application/pdf') {
                // Hide image preview and show PDF buttons
                imageElement.hide();
                pdfButtons.show();
            } 
            // Check if the file is an image
            else if (file.type.startsWith('image/')) {
                // Hide PDF buttons and show image preview
                pdfButtons.hide();
                imageElement.show();

                // Display the selected image
                const reader = new FileReader();
                reader.onload = function(e) {
                    imageElement.attr('src', e.target.result);
                };
                reader.readAsDataURL(file);
            } 
            else {
                // Handle other types if necessary
                pdfButtons.hide();
                imageElement.hide();
            }
        }
    });
});

// Function to render the chart
function renderChart(compliancePercentage) {
    // Ensure the chart container exists
    const chartContainer = document.querySelector("#radialGradient");
    if (!chartContainer) {
        console.error("Chart container not found!");
        return;
    }

    const complianceParsed = parseFloat(String(compliancePercentage).replace(/[^\d.-]/g, ''));
    const normalizedCompliance = Math.max(0, Math.min(100, Number.isFinite(complianceParsed) ? complianceParsed : 0));

    if (complianceChart) {
        complianceChart.destroy();
        complianceChart = null;
    }
    chartContainer.innerHTML = "";

    var radialGradientOptions = {
        series: [Number(normalizedCompliance.toFixed(2))],
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
                    background: "#e9eef5",
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
                            return Math.round(normalizedCompliance) + "%";
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
                gradientToColors: ["#2E8B57"],
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
    complianceChart = new ApexCharts(chartContainer, radialGradientOptions);
    complianceChart.render();
}
</script>

@endpush