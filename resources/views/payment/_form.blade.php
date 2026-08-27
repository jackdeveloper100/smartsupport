<?php $userId = auth()->id(); ?>

                <form class="ajax-form" method="post" action="{{ route('contractors/document/save-account') }}" enctype="multipart/form-data" id="ajax-form">
                @csrf
                <input type = 'hidden' name="user_id" value = "{{$userId}}">
                <input type = 'hidden' name ="id" value="{{@$model->id}}">
                <div class = "row">
                    
                    <div class="col-md-6">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="body" for="rejected_reason">Bank Name <span class="star">*</span></label>
                                <input type="text" class="form-control" id="bank_name" name="bank_name" value="{{@$model->bank_name}}" placeholder="Enter Bank Name" />
                            </div>
                        </div>
                        <div class="col-md-12">
                             <div class="form-group">
                                <label class="body" for="rejected_reason">Account Number <span class="star">*</span></label>
                                <input type="text" class="form-control" id="acc_number" name="account_number" value="{{@$model->account_number}}" placeholder="Enter Account Number" />
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="body" for="rejected_reason">Routing Number <span class="star">*</span></label>
                                <input type="text" class="form-control" id="routing_number" name="routing_number" value="{{@$model->routing_number}}" placeholder="Enter Routing Number" />
                            </div>
                         </div>
                    </div>
                    
                    <div class="col-md-6">
                        <?php if (!empty($model->file_name)) { ?>
                            <div class="img-preview mb-4">
                                <img src="{{ route('document/show', ['fileName' => $model->file_name]) }}" class="img-fluid preview-image payment-upload-img" id="image">
                            </div>
                        <?php } else { ?>
                            <div class="img-preview mb-4">
                                <img src="{{ $general->getNoFile() }}" class="img-fluid payment-upload-img payment-upload-img" id="image">
                            </div>
                        <?php } ?>
                        <div class="form-group">
                            <label clas="body">Image <span class="star">*</span></label>
                            <input type="file" class="form-control" accept="image/*" name="image" onchange="previewImage(this,'#image')">
                        </div>
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
                        <h5 class="modal-title" id="imageModalLabel">Image Preview</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body text-center">
                        <img src="" id="modalImage" class="img-fluid popup-img"/>
                      </div>
                    </div>
                  </div>
                </div>

    @push('scripts')
    <script type="text/javascript">
        documentReady(function() {
            $('.ajax-form').validate({
                submitHandler: function(form) {
                    app.ajaxFileForm(form);
                }
            })
            
             $(".preview-image").on("click", function() {
            var imgSrc = $(this).attr("src");
            $("#modalImage").attr("src", imgSrc);
            $('#imageModal').modal('show');
        });
        });
    </script>
    @endpush