<?php
if (isset($_GET['partial']) && $_GET['partial']) {
    if (isset($_GET['layout']) && $_GET['layout'] == 'main') {
        ?>
        <div id="main-content" data-title="@yield('title') | {{config('setting.app_name')}}">
            {{ view('common/message_alert') }}
            @stack('styles')
            @yield('content')
            @stack('scripts')
        </div>
    <?php
    } else {
        echo 'reload';
    }
} else {
    $metaTags = $general->getMetaTags();
    $sessionUser = false;
    if (!auth()->guest()) {
        $sessionUser = auth()->user();
    }
     $companyApprovedStatus = $sessionUser->company_approved_status; 
    ?>
    <!DOCTYPE html>
    <html lang="{{ Config::get('app.locale') }}" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="theme/assets/" data-template="horizontal-menu-template">

    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
        <base href="{{ URL::to('/') }}/">
        <meta http-equiv="Content-Language" content="{{ Config::get('app.locale') }}">
        @if($metaTags)
        {!! $metaTags !!}
        @else
        <title>@yield('title') | {{config('setting.app_name')}}</title>
        @endif
        
        <link rel="shortcut icon" href="{{ $general->getFileUrl(config('setting.app_fevicon'),'setting') }}"
        type="image/x-icon">
            <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />

        <link rel="stylesheet" href="theme/extensions/simple-datatables/style.css">
        <link rel="stylesheet"  href="theme/compiled/css/table-datatable.css">
        <!-- Icons -->
        <link rel="stylesheet" href="theme/compiled/css/app.css" />
        <link rel="stylesheet" href="theme/compiled/css/app-dark.css" />
        <link rel="stylesheet" href="theme/compiled/css/iconly.css" />
        <link rel="stylesheet" href="assets/css/custom.css" />
        <link rel="stylesheet" href="theme/extensions/@fortawesome/fontawesome-free/css/all.min.css">

        <link rel="stylesheet" href="theme/extensions/sweetalert2/sweetalert2.min.css">
        <link rel="stylesheet" crossorigin href="./theme/compiled/css/extra-component-sweetalert.css">
     
        <link rel="stylesheet" href="theme/extensions/toastify-js/src/toastify.css">
        <link rel="stylesheet" href="theme/extensions/filepond-plugin-image-preview/filepond-plugin-image-preview.css">
        <link rel="stylesheet" href="theme/extensions/datatables.net-bs5/css/dataTables.bootstrap5.min.css">
 
        <link rel="stylesheet" crossorigin href="./theme/compiled/css/table-datatable-jquery.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.15.10/sweetalert2.min.css" integrity="sha512-Of+yU7HlIFqXQcG8Usdd67ejABz27o7CRB1tJCvzGYhTddCi4TZLVhh9tGaJCwlrBiodWCzAx+igo9oaNbUk5A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css" integrity="sha512-UtLOu9C7NuThQhuXXrGwx9Jb/z9zPQJctuAgNUBK3Z6kkSYT9wJ+2+dh6klS+TDBCV9kNPBbAxbVD+vCcfGPaA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <!-- Page CSS -->

        @stack('styles')
        <script>
            var APP_UID = '{{config("setting.app_uid")}}';
            var CSRF_NAME = '_token';
            var CSRF_TOKEN = "{{ Session::token() }}";
            var dataTableObj = false;
            var documentReadyFunctions = [];

            function documentReady(fn) {
                documentReadyFunctions.push(fn);
            }
        </script>
        <!--{!! config('setting.header_content') !!}-->
                <script src="theme/static/js/initTheme.js"></script>
    </head>

    <body>
        <div id="app">
            <aside id="sidebar" class="sidebar_click">
                 <div class="sidebar-wrapper active">
                    <div class="sidebar-header position-relative">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="logo">
                             <a href="{{ route('account/update') }}">
                            <img src="{{$general->getFileUrl(config('setting.app_logo'),'setting')}}" alt="{{ Config::get('setting.app_name') }}"  />
                            </a>
                        </div>
                            <div class="theme-toggle d-flex gap-2  align-items-center mt-0">
                                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true"
                                    role="img" class="iconify iconify--system-uicons" width="20" height="20"
                                    preserveAspectRatio="xMidYMid meet" viewBox="0 0 21 21">
                                    <g fill="none" fill-rule="evenodd" stroke="currentColor" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path
                                            d="M10.5 14.5c2.219 0 4-1.763 4-3.982a4.003 4.003 0 0 0-4-4.018c-2.219 0-4 1.781-4 4c0 2.219 1.781 4 4 4zM4.136 4.136L5.55 5.55m9.9 9.9l1.414 1.414M1.5 10.5h2m14 0h2M4.135 16.863L5.55 15.45m9.899-9.9l1.414-1.415M10.5 19.5v-2m0-14v-2"
                                            opacity=".3"></path>
                                        <g transform="translate(-210 -1)">
                                            <path d="M220.5 2.5v2m6.5.5l-1.5 1.5"></path>
                                            <circle cx="220.5" cy="11.5" r="4"></circle>
                                            <path d="m214 5l1.5 1.5m5 14v-2m6.5-.5l-1.5-1.5M214 18l1.5-1.5m-4-5h2m14 0h2"></path>
                                        </g>
                                    </g>
                                </svg>
                                <div class="form-check form-switch fs-6">
                                    <input class="form-check-input  me-0" type="checkbox" id="toggle-dark" style="cursor: pointer">
                                    <label class="form-check-label"></label>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true"
                                    role="img" class="iconify iconify--mdi" width="20" height="20" preserveAspectRatio="xMidYMid meet"
                                    viewBox="0 0 24 24">
                                    <path fill="currentColor"
                                        d="m17.75 4.09l-2.53 1.94l.91 3.06l-2.63-1.81l-2.63 1.81l.91-3.06l-2.53-1.94L12.44 4l1.06-3l1.06 3l3.19.09m3.5 6.91l-1.64 1.25l.59 1.98l-1.7-1.17l-1.7 1.17l.59-1.98L15.75 11l2.06-.05L18.5 9l.69 1.95l2.06.05m-2.28 4.95c.83-.08 1.72 1.1 1.19 1.85c-.32.45-.66.87-1.08 1.27C15.17 23 8.84 23 4.94 19.07c-3.91-3.9-3.91-10.24 0-14.14c.4-.4.82-.76 1.27-1.08c.75-.53 1.93.36 1.85 1.19c-.27 2.86.69 5.83 2.89 8.02a9.96 9.96 0 0 0 8.02 2.89m-1.64 2.02a12.08 12.08 0 0 1-7.8-3.47c-2.17-2.19-3.33-5-3.49-7.82c-2.81 3.14-2.7 7.96.31 10.98c3.02 3.01 7.84 3.12 10.98.31Z">
                                    </path>
                                </svg>
                             </div>
                    </div>
                </div>
            
                <div class="sidebar-menu">
                    <ul class="menu">
                        @if($companyApprovedStatus == 1)
                        <li class="sidebar-item pjax {{ $general->routeMatchClass('contractor/document') }}">
                            <a href="{{ route('contractor/document') }}" class="sidebar-link pjax sidebar_hide">
                            <i class="bi bi-file-earmark"></i>
                            <div data-i18n="My Document"><span>My Document</span></div>
                            </a>
                        </li>
                        @endif


                       @if($sessionUser)
                        <li class="sidebar-item  has-sub pjax {{ $general->routeMatchClass(['account/update'], 'open') }}">
                            <a href="javascript:void(0);" class="sidebar-link  pjax">
                            <i class="bi bi-person-circle"></i>
                                <div data-i18n="My Account"><span>Profile</span></div>
                            </a>
                            <ul class="submenu active"> 
                                <li class="submenu-item {{ $general->routeMatchClass('account/update')}} ">
                                    <a href="{{ route('account/update') }}" class="submenu-link pjax sidebar_hide">Account</a>
                                </li>
                                <li class="submenu-item {{ $general->routeMatchClass('account/password-change')}} ">
                                    <a href="{{ route('account/password-change') }}" class="submenu-link pjax sidebar_hide">Security </a>
                                </li>
                            
                                <li class="submenu-item {{ $general->routeMatchClass('account/device')}} ">
                                    <a href="{{ route('account/device') }}" class="submenu-link pjax sidebar_hide">Device</a>
                                </li>
                                <li class="submenu-item {{ $general->routeMatchClass('account/log')}} ">
                                    <a href="{{ route('account/log') }}" class="submenu-link pjax sidebar_hide">Log</a>
                                </li>
                                
                            </ul>
                         </li>
                         @endif
                            <li class="sidebar-item pjax {{ $general->routeMatchClass('page/contact') }}">
                            <a href="{{ route('page/contact','help-information') }}" class="sidebar-link pjax sidebar_hide">
                            <i class="bi bi-envelope"></i>
                            <div data-i18n="Help"><span>Help</span></div>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
             </aside>
        <!-- Layout wrapper -->
            <div id="main" class='layout-navbar navbar-fixed'>
                <header>
                    <nav class="navbar navbar-expand navbar-light navbar-top" >
                        <div class="container-fluid">
                         <div class="d-flex align-items-center gap-2">
                            <a  class="burger-btn d-block sidebar_show" >
                                <i class="bi bi-justify fs-3"></i>
                            </a>

                            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                                aria-expanded="false" aria-label="Toggle navigation">
                                <span class="navbar-toggler-icon"></span>
                            </button>
                            @if (session('came_from_admin'))
                                <a href="{{ route('admin/login-back') }}" class="d-flex align-items-center gap-1 mt-2 ms-4" ><dt class="the-icon"><span class="fa-fw select-all fas"></span></dt>
                            Back to Admin</a>
                            @endif
                            
                             @if (session('came_from_company'))
                                <a href="{{ route('company/login-back') }}" class="d-flex align-items-center gap-1 mt-2 ms-4" ><dt class="the-icon"><span class="fa-fw select-all fas"></span></dt>
                            Back to Company</a>
                            @endif
                        </div>
                            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-xl-none">
                                    <i class="fa fa-x  align-middle"></i>
                                </a>
                            <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
                                <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                                    <i class="fa fa-menu-2 "></i>
                                </a>
                            </div>

                            
                            <div class="navbar-nav-right d-flex align-items-center gap-2" id="navbar-collapse">
                               
                                @if($sessionUser)
                                    <div id="google_translate_element" class="google-translate "  ></div>
                                    <a id="change_language_btn" type="button"><i class="bi bi-translate"> Language </i></a>
                                @endif

                                <ul class="navbar-nav flex-row align-items-center ms-auto">
                                    <?php if ($sessionUser) { ?>
                                      <li class="nav-item dropdown me-3">
                                            <a class="nav-link active dropdown-toggle text-gray-600" href="#" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                                                <i class='bi bi-bell bi-sub fs-4'></i>
                                                <span class="badge badge-notification bg-danger">
                                                        <?php echo  (new \App\Models\Notification())->getNotificationCount($sessionUser->id) ?>
                                                        </span>
                                            </a>
                                            <ul class="dropdown-menu dropdown-center  dropdown-menu-sm-end notification-dropdown" aria-labelledby="dropdownMenuButton">
                                                <li class="dropdown-header">
                                                    <h6>Notifications</h6>
                                                </li>
                                                <?php 
                                                    $notificationData = (new \App\Models\Notification())->getLatestNotification($sessionUser->id);
                                                    $notificationDataCount = $notificationData->count(); 
                                                ?>
                                                @if($notificationDataCount > 0)
                                                    @foreach ($notificationData as $notificationData)
                                                    <?php $status = $notificationData->document_status ?>
                                                    <li class="dropdown-item notification-item">
                                                        <a class="d-flex align-items-center " href="contractor/notifications">
                                                        <div class="notification-icon 
                                                        @if($status == 1) bg-success @elseif($status == 2) bg-danger @else bg-warning @endif d-flex align-items-center">
                                                                @if($status == 1)
                                                                    <i class="bi bi-check-circle d-flex align-items-center justify-content-center w-100"></i>
                                                                @elseif($status == 2)
                                                                    <i class="bi bi-x-circle d-flex align-items-center justify-content-center w-100"></i>
                                                                @else
                                                                    <i class="bi bi-hourglass-split d-flex align-items-center justify-content-center w-100"></i>
                                                                @endif
                                                                </div>
                                                            <div class="notification-text ms-sm-4 ms-3">
                                                                <p class="notification-title font-bold">{{$notificationData->title}}</p>
                                                                <p class="notification-subtitle font-thin text-sm">{{$notificationData->description}}</p>
                                                            </div>
                                                        </a>
                                                    </li>
                                                    @endforeach
                                                        <li>
                                                            <p class="text-center py-2 mb-0"><a href="contractor/notifications">See All Notification</a></p>
                                                        </li>
                                                @else
                                                    <li class="dropdown-item">
                                                        <p class="text-center py-2 mb-0">No Notifications</p>
                                                    </li>      
                                                @endif
                                            </ul>
                                        </li>
                                        <li class="nav-item navbar-dropdown dropdown-user dropdown">
                                                        <a href="#" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <div class="user-menu d-flex align-items-center">
                                                        <div class="user-name text-end me-sm-3 me-1">
                                                        <h6 class="mb-0 text-gray-600 mt-1">{{ $sessionUser->first_name.' '.$sessionUser->last_name }}</h6>
                                                        </div>
                                                        <div class="user-img d-flex align-items-center">
                                                            <div class="avatar avatar-md me-0">
                                                            <img src="{{ $general->getFileUrl($sessionUser->image,'profile') }}" alt class="rounded-circle" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                </a>
                                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton" style="min-width: 11rem;">
                                                <li>
                                                    <h6 class="dropdown-header">Hello, {{ $sessionUser->first_name.' '.$sessionUser->last_name }} !</h6>
                                                    <h6 class="dropdown-header"> {{ $sessionUser->email}} </h6>
                                                </li>    
                                                <li>
                                                <a class="dropdown-item" href="{{ route('account/update') }}"> <i class="icon-mid bi bi-person me-2"></i> My Account</a> </li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <a class="dropdown-item " href="{{ route('logout') }}" id="logout-btn"> <i class="icon-mid bi bi-box-arrow-left me-2"></i> Logout</a>
                                                 </li>
                                            </ul>
                                        </li>
                                    <?php } else { ?>
                                        <li class="menu-item {{ $general->routeMatchClass('login')}}">
                                            <a href="login" class="menu-link">
                                                <div data-i18n="Login" style="padding-right: 10px;">Login</div>
                                            </a>
                                        </li>
                                        <br>
                                        <li class="menu-item {{ $general->routeMatchClass('account/register')}}">
                                            <a href="account/register" class="menu-link">
                                                <div data-i18n="Register">Register</div>
                                            </a>
                                        </li>
                                    <?php } ?>
                                    <!--/ User -->
                                </ul>
                            </div>
                            <!-- Search Small Screens -->
                            <div class="navbar-search-wrapper search-input-wrapper container-xxl d-none">
                                <input type="text" class="form-control search-input border-0" placeholder="Search..." aria-label="Search..." />
                                <i class="fa fa-x  search-toggler cursor-pointer"></i>
                            </div>
                        </div>
                    </nav>
                </header>

                        <div class="main-content">
                            <div class="page-heading">
                                <div class="section">
                                    <div id="main-container" data-layout="main">
                                        <div id="main-content" data-title="@yield('title') | {{config('setting.app_name')}}">
                                            {{ view('common/message_alert') }}
                                            @yield('content')
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Footer -->
                        <footer class="content-footer footer bg-footer-theme">
                            <div>
                                <div class="footer-container d-flex align-items-center justify-content-between py-2 flex-md-row flex-column">
                                    <div>
                                        ©{{date('Y')}} , made by <a href="{{route('contractor/document')}}" target="_blank" class="fw-semibold">{{ config('setting.app_name') }}</a>
                                    </div>
                                    <div>
                                        <a target="_blank" href="page/terms-conditions">Terms & Condition</a> |
                                        <a target="_blank" href="page/privacy-policy">Privacy Policy</a>
                                    </div>
                                </div>
                            </div>
                        </footer>
                        <!-- / Footer -->
                    </div>
                    <!--/ Content wrapper -->
                </div>
                <!--/ Layout container -->
            </div>
            <!-- Overlay -->
            <div class="layout-overlay layout-menu-toggle"></div>
            <div class="drag-target"></div>
        </div>
        <!--/ Layout wrapper -->
        <div class="modal fade" id="common-modal">
            <div class="modal-dialog">
                <div class="modal-content" id="common-modal-content">
                </div>
            </div>
        </div>
    </div>    
   
        <!-- Core JS -->
        {{view('common/cookie_consent')}}
                <script src="theme/static/js/components/dark.js"></script>
        
        <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
        <script src="theme/extensions/perfect-scrollbar/perfect-scrollbar.min.js"></script>
        <script src="theme/compiled/js/app.js"></script>
        <script src="theme/extensions/toastify-js/src/toastify.js"></script>
        <!-- <script src="theme/static/js/pages/toastify.js"></script> -->

        <script src="assets/js/jquery.js"></script>
        <script src="assets/js/app.js"></script>
        <script src="theme/extensions/datatables.net/js/jquery.dataTables.min.js"></script>
        
        <!-- <script src="theme/extensions/simple-datatables/umd/simple-datatables.js"></script>
        <script src="theme/static/js/pages/simple-datatables.js"></script> -->

        <script src="theme/extensions/flatpickr/flatpickr.min.js"></script>
        <script src="theme/static/js/pages/date-picker.js"></script>
        <!-- endbuild -->
        <!-- Main JS -->

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.21.0/jquery.validate.min.js" integrity="sha512-KFHXdr2oObHKI9w4Hv1XPKc898mE4kgYx58oqsc/JqqdLMDI4YjOLzom+EMlW8HFUd0QfjfAvxSL6sEq/a42fQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.15.9/sweetalert2.min.js" integrity="sha512-42SOMmTiQryVFk+kJc8Mk1YCoPYvTSX1KCz7sZOGGFcBzytpPLeKuF6AOOQvln5zrUBDjJqshCdMGYRVC/BsYg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js" integrity="sha512-JyCZjCOZoyeQZSd5+YEAcFgz2fowJ1F1hyJOXgtKu4llIa0KneLcidn5bwfutiehUTiOuK87A986BZJMko0eWQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>


        <script src="theme/extensions/sweetalert2/sweetalert2.min.js"></script>
        <script src="theme/static/js/pages/sweetalert2.js"></script>

        <script src="theme/extensions/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
        <script src="theme/static/js/pages/datatables.js"></script>
        
        <script src="theme/extensions/apexcharts/apexcharts.min.js"></script>
        <script src="theme/static/js/pages/ui-apexchart.js"></script>
        <script src="theme/static/js/pages/dashboard.js"></script>

        <script src="assets/js/pjax.js"></script>   
                                        

        <!-- <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script> -->

        @stack('scripts')
        <script>
            $(document).ready(function() {
                runDocumentReady();
            });
            

        </script>
        <script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

        <!--{!! config('setting.footer_content') !!}-->
    </body>

    </html>
<?php } ?>  