<style>
.modal-dialog, html[data-bs-theme="dark"] .modal-dialog {
    height: 90vh;
    display: flex;
    align-items: center;
}

@media only screen and (max-width: 992px) {
    .modal-dialog, html[data-bs-theme="dark"] .modal-dialog {
        max-width: 93%;
    }
}
</style>
<div class="modal-header">
    <h5 class="modal-title" id="myModalLabel1">Contractor Registration Request</h5>
    <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
    </button>
</div>
<div class="modal-body">
    <div style="text-align:center">
            <h5>{{$user->first_name}} {{$user->last_name}} has requested to register as a contractor with your company.</h5>
              <p>Please review the request and choose an action:</p>

            <button class="btn btn-success" onclick="app.confirmApproveAction(this);" data-action="company/contractor/register-approve" data-id = "{{$user->id}}">Approve</button>
            <button class="btn btn-danger"  onclick="app.confirmRejectAction(this);" data-action="company/contractor/register-reject" data-id = "{{$user->id}}">Reject</button>
    </div>
</div>


