@extends('layouts.main')
@section('title')
Document View
@endsection
@section('content')

<div class="page-title">
    <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
            <h3>Document</h3>
        </div>
        <div class="col-12 col-md-6 order-md-2 order-first">
            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-sm-end">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="contractor/document" class="pjax">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="contractor/document" class="pjax">Document</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Document View</li>
                </ol>
            </nav>
        </div>
    </div>
</div>


<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
                <h5 class="card-title">Document View</h5>
                @if(@$extension == 'pdf')
                <div class="d-flex align-items-center flex-wrap gap-2 justify-content-between">
                   
                <a href="javascript:void(0)" 
                class="btn btn-primary act-btns tool-btn me-2" 
                onclick="app.showModalView('{{ route('contractor/document-file/preview', ['id' => @$model->id, 'type' => 'documentId']) }}')">Preview</a>

                <a href="{{ route('document/show', ['fileName' => $fileName]) }}"
                class="btn btn-success" 
                download>Download</a>

                </div>
                @endif
            </div>
            <div class="card-body">
                <div class="row">
                    @if(@$extension == 'pdf')
                        <div class="col-12 wrap-tbl">
                    @else
                        <div class="col-lg-6 col-md-6 col-12">
                    @endif
                        <table class="table">
                            <tr>
                                <th>Contractor Name :</th>
                                <td>{{$userData->first_name}} {{$userData->last_name}}</td>
                            </tr>
                            <tr>
                                <th>Business Name :</th>
                                <td>{{$userData->business_name}}</td>
                            </tr>
                            <tr>
                                <th>Document Name :</th>
                                <td>{{$documentModel->getDocumentName($model->type)}}</td>
                            </tr>
                            <tr>
                                <th>Contractor Status :</th>
                                <td>{!! (new \App\Models\User())->getStatusBadge($userData->status) !!}</td>
                            </tr>
                            <tr>
                                <th>Document Status :</th>
                                <td>{!! $documentModel->getApprovedStatusBadge($model->approve_status) !!}</td>
                            </tr>
                            <tr>
                                <th>Current Document Version :</th>
                                <td>{{$currentVersion}}</td>
                            </tr>
                            <tr>
                                <th>Expired Date :</th>
                                <td>{{$model->expired_at}}</td>
                            </tr>
                            <tr>
                                <th>Created Date :</th>
                                <td>{{$model->created_at}}</td>
                            </tr>
                        </table>
                    </div>
    
                    @if(@$extension !== 'pdf')
                        <div class="col-lg-6 col-md-6 col-12 mt-2 mt-md-0 mb-md-0 mb-2">
                            <div class="view-img"{{ $general->getFileUrl($fileName,'document') }}>
                                <a href="javascript:void(0)" 
                                class="act-btns tool-btn me-2" 
                                onclick="app.showModalView('{{ route('contractor/document-file/preview', ['id' => @$model->id, 'type' => 'documentId']) }}')">
                                    <img class=" " 
                                        src="{{ $general->getFileUrl($fileName,'document') }}" 
                                        alt="Image Preview">
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="row gallery" data-bs-toggle="modal" data-bs-target="#galleryModal"></div>
            </div>
        </div>
    </div>
</div>

    <section class="section">
        <div class="card">
            <div class="card-header justify-content-between d-flex align-items-center flex-wrap gap-2 ">
                <h5 class="card-title">
                    Documents Version
                </h5>  
            </div>

            <div class="card-body dataTable-container">
                <table class="datatable-list-table table border-top" id="data-table">
                    <thead>
                        <tr>
                        <th>Id</th>
                        <th>Document Versions</th>
                        <th>Date</th>
                        <th>Action </th>
                        </tr>
                    </thead>
                </table>
            </div>
            
        </div>
    </section>

@endsection
@push('scripts')
     <script type="text/javascript">
        documentReady(function(){
            datatableObj = $('#data-table').DataTable({
             pageLength: 25, 
         lengthMenu: [25, 50, 100], 
         stateSave :true,
                ajax: {
                    url: '{{route("contractor/documents-version",['id'=>@$model->id])}}',
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
                    data: "document_version",
                    responsivePriority:2,
                    bSortable: false
                    }, //,visible:false
                    {
                    data: "created_at",
                    bSortable: true,
                    responsivePriority: 1
                    },
                    {
                    data: "action",
                    bSortable: false,
                    responsivePriority: 1
                    }
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