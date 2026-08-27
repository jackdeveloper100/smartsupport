<div class="modal-lg">
    <div class="modal-header">
        <h5 class="modal-title" id="myModalLabel1">Request W-9 Create</h5>
        <button type="button" class="btn-close rounded-pill" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body p-0">
        <div class="card card-default color-palette-box m-0">
            <div class="card-body">
                <?= view('company/vendor/_form', ['user' => $user, 'contractorList' => $contractorList,]) ?>
            </div>
        </div>
    </div>
</div>
