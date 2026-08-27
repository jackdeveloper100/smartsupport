<style>
    button.close {
        border: 0;
        background-color: unset;
        font-size: 22px;
        float: right;
    }

    .modal-content .card-header h5 {
        margin-bottom: 0;
    }

    .modal-content .card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: var(--bs-card-border-width) solid var(--bs-card-border-color);
    }

    .modal-content .btn-submit {
        float: right;
        margin: 0 16px 16px 16px;
    }

    .card-header{
        background-color: #fff;
    }

</style>
<style>
    .star-rating input[type="radio"] {
        display: none;
    }
    .star-container {
        display: flex;
        flex-direction: row-reverse;
    }
    .star-container label {
        font-size: 24px;
        cursor: pointer;
        color: #ccc;
        margin-left: 5px;
    }
    .star-container input[type="radio"]:checked ~ label {
        color: red !important;
    }
</style>
<div class="card card-default color-palette-box">
    <div class="card-header">
        <h5 class="card-title">
            Content Update
        </h5>
        <button type="button" class="close closebtntxtchn" data-bs-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <style>
        .ck.ck-content.ck-editor__editable.ck-rounded-corners.ck-editor__editable_inline.ck-blurred p {
            color: #000 !important;
        }

        .ck.ck-content.ck-editor__editable.ck-rounded-corners.ck-editor__editable_inline.ck-focused p {
            color: #000 !important;
        }
    </style>

    <form action="admin/content/save" class="content-form" id="content-form" method="post" enctype="multipart/form-data">
        @csrf
            <input type="hidden" name="id" value="{{ @$model->id }}">
            <?php
            if ($model->type == 'footer') {
                echo view('admin/content/footer', ['model' => $model]);
            } elseif ($model->type == 'services') {
                 echo view('admin/content/services', ['model' => $model]);
            } elseif ($model->type == 'rating') {
                 echo view('admin/content/rating', ['model' => $model]);
            } elseif ($model->type == 'header') {
                 echo view('admin/content/header', ['model' => $model]);
            }else {
                echo view('admin/content/_form', ['model' => $model]);
            }?>

        <button type="submit" class="btn-submit btn btn-primary ">Submit</button>

    </form>
</div>
<script>
    var ckeditorObj=false;
    documentReady(function(){
        $('.content-form').validate({
            submitHandler: function(form) {
                app.ajaxFileForm(form);
                if(ckeditorObj){
                    $('#content-ckeditor').html(ckeditorObj.getData())
                }
            }
        })
    })
    
</script>