<style>
    @media (min-width: 576px) {
    .modal-dialog {
        max-width: 68%;
        margin-right: auto;
        margin-left: auto;
    }
}
</style>
<div class="modal-header ">
    <h5 class="modal-title" id="myModalLabel1">Document View</h5>
      <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
    </button>
</div>

<div class = "modal-body">
<section class="section">
    <div class="row">
        <div class="col-12">
            <div class="card mb-0">
                <div class="card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
                    <h5 class="card-title d-none">Document View</h5>
                   
                        <!-- <button class="btn btn-primary" id="previewButton">Preview</button> -->
                         <div class="d-flex ms-auto align-items-center flex-wrap gap-2 justify-content-between">
                         <a href="javascript:void(0)" 
                            class="btn btn-primary act-btns tool-btn me-2" 
                            onclick="app.showModalView('{{ route('admin/contractor/document-file/preview', ['id' => @$model->id, 'type' => 'documentId']) }}')">Preview</a>
                            <a href="{{ route('admin/document/show', ['fileName' => $fileName]) }}" class="btn btn-success" download>Download</a>
                        </div>
                 
                </div>
                <div class="card-body">
                    <div class="row">
                       
                            <div class="col-12">
                      
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
                                    <th>Approved Status :</th>
                                    <td>{!! $documentModel->getApprovedStatusBadge($model->approve_status) !!}</td>
                                </tr>
                                <tr>
                                    <th>Current Document Version :</th>
                                    <td>{{$currentVersion}}</td>
                                </tr>
                                <tr>
                                    <th>Expired Date :</th>
                                    <td>
                                     @if(empty($model->expired_at))
                                        Never
                                    @else
                                        {{$model->expired_at}}
                                    @endif
                                       
                                    </td>
                                </tr>
                                <tr>
                                    
                                    <th>Created Date :</th>
                                    <td>{{$model->created_at}}</td>
                                </tr>
                            </table>
                        </div>

                      
                    </div>

                    <div class="row gallery" data-bs-toggle="modal" data-bs-target="#galleryModal">
                        <!-- Gallery Content Goes Here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


    <section class="section">
        <div class="card">
            <div class="card-header justify-content-between d-flex align-items-center flex-wrap gap-2 ">
                <h5 class="card-title">
                    Documents Version
                </h5>  
            </div>
            
            <div class="card-body dataTable-container">
                <table class="datatable-list-table table border-top" id="data-table-history">
                    <thead>
                        <tr>
                        <th>Id</th>
                        <th>Document Versions</th>
                        <th>Date</th>
                        <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </section>
</div>

     <script type="text/javascript">
         documentReady(function(){
            datatableObj = $('#data-table-history').DataTable({
         pageLength: 25, 
         lengthMenu: [25, 50, 100],
         stateSave :true,
                ajax: {
                    url: '{{route("admin/contractor/documents-version",['id'=>@$model->id])}}',
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
           

