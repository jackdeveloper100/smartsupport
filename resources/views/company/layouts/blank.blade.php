<?php
if (isset($_GET['partial']) && $_GET['partial']) {
  if (isset($_GET['layout']) && $_GET['layout'] == 'blank') {
?>
    <div id="main-content" data-title="@yield('title') | {{ Config::get('setting.app_name') }}">
      {{ view('common/message_alert') }}
      @stack('styles')
      @yield('content')
      @stack('scripts')
    </div>
  <?php } else {
    echo 'reload';
  }
} else {
  ?>
  <!DOCTYPE html>

  <html lang="{{ Config::get('app.locale') }}" class="light-style layout-navbar-fixed layout-menu-fixed" dir="ltr" data-theme="theme-default" data-theme/assets-path="/tracer/public/theme/assets/" data-template="vertical-menu-template">

  <head>
    <meta charset="utf-8" />
    <base href="{{URL::to('/')}}/">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>@yield('title') | {{config('setting.app_name')}}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ $general->getFileUrl(config('setting.app_fevicon'),'setting') }}" type="image/x-icon">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="assets/css/custom.css" />
    <link rel="stylesheet" href="theme/compiled/css/app.css" />
    <link rel="stylesheet" href="theme/compiled/css/app-dark.css" />
    <link rel="stylesheet" href="theme/compiled/css/auth.css" />
    <link rel="stylesheet" href="theme/extensions/toastify-js/src/toastify.css">

    <link rel="stylesheet" href="theme/extensions/sweetalert2/sweetalert2.min.css">
    <link rel="stylesheet" crossorigin href="./theme/compiled/css/extra-component-sweetalert.css">

    <!-- Core CSS -->
 
    
    <!-- Vendor -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.15.10/sweetalert2.min.css" integrity="sha512-Of+yU7HlIFqXQcG8Usdd67ejABz27o7CRB1tJCvzGYhTddCi4TZLVhh9tGaJCwlrBiodWCzAx+igo9oaNbUk5A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        
    <!-- Page CSS -->
    
    <!-- Page -->
    <script src="theme/static/js/initTheme.js"></script>

    @stack('style')
    <!-- Helpers -->

    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
  </head>
  <script>
    
    /*Global variables*/
    var APP_UID = '{{config("setting.app_uid")}}';
    var CSRF_NAME = '_token';
    var CSRF_TOKEN = "{{ Session::token() }}";
    var dataTableObj = false;
    var documentReadyFunctions = [];
    function documentReady(fn) {
      documentReadyFunctions.push(fn);
    }
  </script>
  </head>

  <body>
    <!-- Layout wrapper -->
    <div id="auth">
      <div id="main-container" data-layout="blank">
        <div id="main-content" data-title="@yield('title') | {{config('app.name')}}">
          @yield('content')
        </div>
      </div>
    </div>
    
    <div class="modal fade" id="common-modal">
        <div class="modal-dialog">
            <div class="modal-content" id="common-modal-content">
            </div>
        </div>
    </div>

    <!-- / Layout wrapper -->
    <!-- Core JS -->
    <!-- build:js theme/assets/vendor/js/core.js -->

    <!-- endbuild -->
    <!-- Vendors JS -->
    <!-- Main JS -->
    
    <!-- Page JS -->
    <script src="assets/js/jquery.js"></script>
        
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.21.0/jquery.validate.min.js" integrity="sha512-KFHXdr2oObHKI9w4Hv1XPKc898mE4kgYx58oqsc/JqqdLMDI4YjOLzom+EMlW8HFUd0QfjfAvxSL6sEq/a42fQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.15.9/sweetalert2.min.js" integrity="sha512-42SOMmTiQryVFk+kJc8Mk1YCoPYvTSX1KCz7sZOGGFcBzytpPLeKuF6AOOQvln5zrUBDjJqshCdMGYRVC/BsYg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    
    <!-- <script src="theme/extensions/toastify-js/src/toastify.js"></script>
    <script src="theme/static/js/pages/toastify.js"></script> -->
    <script src="theme/extensions/flatpickr/flatpickr.min.js"></script>

    <script src="theme/extensions/sweetalert2/sweetalert2.min.js"></script>
    <script src="theme/static/js/pages/sweetalert2.js"></script>

    <script src="assets/js/pjax.js"></script>
    <script src="assets/js/app.js"></script>

    @stack('scripts')
    <script>
        $(document).ready(function () {
            runDocumentReady();
        });
    </script>
  </body>
  </html>
<?php } ?>