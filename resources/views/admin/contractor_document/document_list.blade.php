<style>
    @media (min-width: 576px) {
    .modal-dialog {
        max-width: 68%;
        margin-right: auto;
        margin-left: auto;
    }
}
</style>

<div class="modal-header">
    <h5 class="modal-title" id="myModalLabel1">Documents List</h5>
    <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
    </button>
</div>


<div class="modal-body" >
    <section class="section">
        <div class="row">
            <div class="col-12">
                <div class="card mb-0">
                    <div class="card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
                            <h5 class="card-title d-none">Document View</h5>
                           
                    </div>
                    <div class="card-body dataTable-container">
                        <table class="datatable-list-table table border-top" id="data-table-doc">
                            <thead>
                                <tr>
                                <th>Id</th>
                                <th>Document Name</th>
                                <th>Expiration Date</th>
                                <th>Status</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

 <script type="text/javascript">
         documentReady(function(){
            datatableObj = $('#data-table-doc').DataTable({
         pageLength: 25, 
         lengthMenu: [25, 50, 100],
         stateSave :true,
                ajax: {
                    url: '{{route("admin/contractor/filtered-docs",['id'=>@$contractorId,'status'=>@$status])}}',
                    method: 'post',
                    dataSrc: 'data',
                    data: {
                    '_token': CSRF_TOKEN
                    }
                },
            
            columns: [
                {data: "id",responsivePriority: 4}, //,visible:false
                {data: "document_id",responsivePriority:2,bSortable: false}, //,visible:false
                {data: "expired_at",bSortable: true,responsivePriority: 1},
                {data: "status",bSortable: false,responsivePriority: 1}
            ],
            responsive: true,
            serverSide: true,
            "order": [
                [0, "desc"]
            ]
            });
        });
    </script>