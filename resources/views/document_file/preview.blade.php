    <style>
            .modal-dialog{
            max-width: 68% !important;
            height: 90vh;
            display: flex ;
            align-items: center;
            }
            iframe {
            height: 80vh;
            }
            @media only screen and (max-width: 992px) { 
            .modal-dialog{
            max-width: 95% !important;
            }
            }
            @media only screen and (max-width: 576px) { 
            .modal-dialog{
            
            margin-top: 20px;
            }
            iframe {
            height: auto;
            }
            }
    </style>

<div class="modal-header">
    <h5 class="modal-title" id="myModalLabel1">Document Preview</h5>
    <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
    </button>
</div>

<div class="modal-body">
    <div style="text-align:center">
        @if($extension == 'pdf')
            <iframe src="{{ route('document/show', ['fileName' => $model->filename]) }} " frameborder="0" height="550px" width="100%"></iframe>
        @else
            <img class="w-100 active " src="{{ route('document/show', ['fileName' => $model->filename]) }}"  alt="Image Preview">
        @endif
    </div>
</div>
