<style>
    .modal-dialog, html[data-bs-theme="dark"] .modal-dialog {
        max-width: 68%;
        height: 90vh;
        display: flex;
        align-items: center;
    }
        
    @media only screen and (max-width: 992px) { 
        .modal-dialog, html[data-bs-theme="dark"] .modal-dialog {
         max-width: 93%;
    }
</style>

<div class="modal-header ">
    <h5 class="modal-title" id="myModalLabel1">Pdf Preview</h5>
      <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
    </button>
</div>
<div class = "modal-body">
<div class="col-md-12">
        <div style="text-align:center; " id="pdfPreviewModal">
                <iframe src="{{ route('admin/document/show', ['fileName' => $fileName]) }}" frameborder="0" height="550px" width="100%"></iframe>
        </div>
    </div>
 </div>
