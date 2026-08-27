@if ($message = Session::get('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            app.showSweetAlertToast(@json($message), 'success');
        });
    </script>
@endif


@if ($message = Session::get('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            app.showSweetAlertToast(@json($message), 'error');
        });
    </script>
@endif


@if ($message = Session::get('warning'))
<script>
        document.addEventListener('DOMContentLoaded', function () {
            app.showSweetAlertToast(@json($message), 'warning');
        });
</script>
@endif


@if ($message = Session::get('info'))

<script>
        document.addEventListener('DOMContentLoaded', function () {
            app.showSweetAlertToast(@json($message), 'info');
        });
</script>
@endif


@if ($errors->any())
<div class="alert alert-denger alert-dismissible">
    <button type="button" class="close" data-bs-dismiss="alert"></button>
    {!! $errors->first() !!}
</div>
@endif

