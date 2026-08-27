@extends('admin.layouts.main')
@section('title')
Seo meta
@endsection
@section('content')
<?php $sessionUser = auth()->user();?>
<!-- Content -->

<div class="page-title">
    <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
            <h3>Seo meta</h3>
        </div>
        <div class="col-12 col-md-6 order-md-2 order-first">
            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="admin/dashboard" class="pjax">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Seo meta</li>
                </ol>
            </nav>
        </div>
    </div>
</div>
<!--  List Table -->
  <section class="section">
    <div class="card">
        <div class="card-header">
        <h5 class="card-title">Seo meta</h5>
        @if($sessionUser->hasPermission('admin/seo/create'))
        <a href="admin/seo/create" class="btn btn-primary d-sm-inline-block pjax" style="float: inline-end;">Create</a>
        @endif
        @if($sessionUser->hasPermission('admin/seo/sitemap-generate'))
        <button type="button" class="btn btn-primary d-sm-inline-block" style="float: inline-end;margin-right: 10px;" onclick="$('#sitemapmodel').modal('show')">Sitemap</button>
        @endif
        </div>
        <div class="card-body">
            <div class="card-datatable table">
                <table class="datatable-list-table table border-top" id="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Url</th>
                            <th>Title</th>
                            <th>Keyword</th>
                            <th>Discription</th>
                            <th>Sitemap</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
         </div>
    </div>
</section>
<!-- / Content -->
<!--Bootstrap Tables-->
@endsection
@push('scripts')
<!-- Modal -->
<div class="modal fade" id="sitemapmodel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sitemapmodellabel">SiteMap</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                SiteMap Url : <a href="{{url('sitemap.xml')}}" class="noroute" target="_blank">{{url('sitemap.xml')}}</a>
                <!-- <button class="btn btn-primary" onclick="app.ajaxGet('admin/seo/sitemap-update');">Update SiteMap</button> -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary " data-bs-dismiss="modal" onclick="app.ajaxGet('admin/seo/sitemap-update');">Update SiteMap</button>
            </div>
        </div>
    </div>
</div>
<script>
    
documentReady(function() {
    datatableObj = $('#data-table').DataTable({
         pageLength: 25, 
         lengthMenu: [25, 50, 100],
         stateSave :true,
        ajax: {
            url: '{{route("admin/seo/list")}}',
            method: 'post',
            dataSrc: 'data',
            data: {
                '_token': CSRF_TOKEN
            }
        },
        columns: [{
                data: "id",
                responsivePriority: 6
            }, //,visible:false
            {
                data: "url",
                responsivePriority: 6
            }, //,visible:false
            {
                data: "title",
                responsivePriority: 6
            }, //,visible:false
            {
                data: "keyword",
                responsivePriority: 4
            },
            {
                data: "description",
                responsivePriority: 4
            },
            {
                data: "sitemap_enable",
                responsivePriority: 4
            },
            {
                data: "action",
                bSortable: false,
                responsivePriority: 2
            }
        ],
        responsive: true,
        serverSide: true,
        "order": [
            [0, "desc"]
        ],
    });
});
</script>
@endpush