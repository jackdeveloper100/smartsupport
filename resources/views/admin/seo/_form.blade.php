
<form class="ajax-form" method="post" action="admin/seo/save" enctype="multipart/form-data" id="ajax-form">
    @csrf
    <input type="hidden" name="id" value="{{ @$model->id }}">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="body" for="url">URL (Without Domain Name) Ex.abc/def <span class="star">*</span></label>
                <input type="text" class="form-control" id="url" placeholder="Enter URL Without Domain Name" name="url" value="{{ @$model->url }}" required />
            </div>
            <div class="form-group">
                <label class="body" for="title">Title <span class="star">*</span></label>
                <input type="text" class="form-control" id="title" placeholder="Title" name="title" value="{{ @$model->title }}" required />
            </div>
            <div class="form-group">
                <label class="body" for="description">Description <span class="form-label star">*</span></label>
                <textarea class="form-control h-50" id="description" placeholder="Description" name="description" rows="3" required>{{ @$model->description }}</textarea>
            </div>
            <div class="form-group">
                <label class="body" for="keyword">Keyword <span class="star">*</span></label>
                <input type="text" class="form-control" id="keyword" placeholder="Keyword" name="keyword" value="{{ @$model->keyword }}" required />
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Site Map</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="site_map" id="frequency_enabled" value="1" data-bs-toggle="collapse" data-bs-target="#collapseFrequency" aria-expanded="false" aria-controls="collapseFrequency" <?= (@$model->sitemap_enable == 1) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="frequency_enabled">
                        Enable
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="site_map" id="frequency_disabled" value="0" data-bs-toggle="collapse" data-bs-target="#collapseFrequency" aria-expanded="false" aria-controls="collapseFrequency" <?= (@$model->sitemap_enable == 0) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="frequency_disabled">
                        Disable
                    </label>
                </div>
            </div>
            <div class="collapse <?= (@$model->sitemap_enable == 1) ? 'show' : ''; ?>" id="collapseFrequency">
                <div class="mb-3">
                    <label class="form-label" for="frequency">Frequency</label>
                    <select class="form-select" name="frequency">
                        <option></option>
                        <option value="weekly" <?php if (@$model->change_frequency === 'weekly') {
                                                    echo 'selected';
                                                } ?>>Weekly</option>
                        <option value="monthly" <?php if (@$model->change_frequency === 'monthly') {
                                                    echo 'selected';
                                                } ?>>Monthly</option>
                        <option value="yearly" <?php if (@$model->change_frequency === 'yearly') {
                                                    echo 'selected';
                                                } ?>>Yearly</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="body" for="priority">Priority</label>
                    <input type="number" class="form-control" id="priority" placeholder="Priority" name="priority" value="{{ @$model->priority }}" />
                </div>
                <div class="form-group">
                    <label class="body" for="priority">Last Modified</label>
                    <input type="datetime" class="form-control" id="priority" placeholder="Priority" name="last_modified" value="{{Carbon\Carbon::now()->format('Y-m-d')." ".Carbon\Carbon::now()->format('H:i:a')}}" />
                </div>
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
    <a href="admin/seo/meta" class="btn btn-dark" style="color:white;">Back</a>
</form>

@push('scripts')
<script type="text/javascript">
    documentReady(function() {
        jQuery.validator.addMethod("alphaOnly", function(value, element) {
            return this.optional(element) || /^[a-zA-Z\s]+$/.test(value);
        }, "Please enter only alphabetic characters");

        $('#ajax-form').validate({
            rules: {
                title: {
                    required: true,
                },
                keyword: {
                    required: true,
                },

            },
            messages: {
                title: {
                    required: "Please enter the title",
                },
                keyword: {
                    required: "Please enter the keyword"
                },
            },

            submitHandler: function(form) {
                app.ajaxFileForm(form);
            },
              errorPlacement: function(error, element) {
                error.insertAfter(element.closest('.mb-3'));
            },
        });

    });
    
</script>
@endpush