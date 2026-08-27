<?php 
$primaryColor = 'green'; 
$sessionUser = auth()->user() ? auth()->user() : '' ;
$companyLogo = $company->company_logo ?? '' ;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="format-detection" content="date=no" />
    <meta name="format-detection" content="address=no" />
    <meta name="format-detection" content="telephone=no" />
    <meta name="x-apple-disable-message-reformatting" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <title>Email Template</title>
 <style type="text/css" media="screen">
        /* Linked Styles */
        * {
            font-family: 'Roboto', sans-serif;
        }

        body {
            padding: 0 !important;
            margin: 0 !important;
            display: block !important;
            min-width: 100% !important;
            width: 100% !important;
            background: #d9d9d9;
            -webkit-text-size-adjust: none
        }

        a {
            color: #000001;
            text-decoration: none
        }

        /* p {
            padding: 0 !important;
            margin: 0 !important
        } */

        img {
            -ms-interpolation-mode: bicubic;
            /* Allow smoother rendering of resized image in Internet Explorer */
        }

        .mcnPreviewText {
            display: none !important;
        }

        .text-footer2 a {
            color: #ffffff;
        }

        .m0 {
            margin: 0px;
        }

        .bg-primary {
            background:#435ebe;
        }
        h2 {
            margin: 20px 0 !important;
            font-size: 28px !important;
            font-weight: 400 !important;
            line-height: 36px !important;
            padding: 0 !important;
        }
        
        p {
            font-weight: 300 !important;
            /* padding: 38px 0 0 0 !important; */
             line-height: 150% !important;

        }
        h6 {
            margin: 10px 0 0 0 !important;
        }
        h6 span {
            font-size: 18px !important;
    
        }

        h5 {
            font-size: 17px !important;
            margin: 20px 0 10px !important;
            color: #000 !important;
            font-weight: 400 !important;
        }
        h4 span {
            margin: 0 !important;
            font-size: 28px !important;

        }
        h2 {
            font-weight: 500 !important;
            color: #000;
        }
        /* Mobile styles */
        @media only screen and (max-device-width: 480px),
        only screen and (max-width: 480px) {
            .mobile-shell {
                width: 100% !important;
                min-width: 100% !important;
            }

            .m-center {
                text-align: center !important;
            }

            .m-left {
                text-align: left !important;
                margin-right: auto !important;
            }

            .center {
                margin: 0 auto !important;
            }

            .content2 {
                padding: 8px 15px 12px !important;
            }

            .t-left {
                float: left !important;
                margin-right: 30px !important;
            }

            .t-left-2 {
                float: left !important;
            }

            .td {
                width: 100% !important;
                min-width: 100% !important;
            }

            .content {
                padding: 30px 15px !important;
            }

            .section {
                padding: 30px 15px 0px !important;
            }

            .m-br-15 {
                height: 15px !important;
            }

            .mpb5 {
                padding-bottom: 5px !important;
            }

            .mpb15 {
                padding-bottom: 15px !important;
            }

            .mpb20 {
                padding-bottom: 20px !important;
            }

            .mpb30 {
                padding-bottom: 30px !important;
            }

            .m-padder {
                padding: 0px 15px !important;
            }

            .m-padder2 {
                padding-left: 15px !important;
                padding-right: 15px !important;
            }

            .p70 {
                padding: 30px 0px !important;
            }

            .pt70 {
                padding-top: 30px !important;
            }

            .p0-15 {
                padding: 0px 15px !important;
            }

            .p30-15 {
                padding: 30px 15px !important;
            }

            /* .p30-15-0 {
                padding: 30px 15px 0px 15px !important;
            } */

            .p0-15-30 {
                padding: 0px 15px 30px 15px !important;
            }


            .text-footer {
                text-align: center !important;
            }

            .m-td,
            .m-hide {
                display: none !important;
                width: 0 !important;
                height: 0 !important;
                font-size: 0 !important;
                line-height: 0 !important;
                min-height: 0 !important;
            }

            .m-block {
                display: block !important;
            }

            .fluid-img img {
                width: 100% !important;
                max-width: 100% !important;
                height: auto !important;
            }

            .column,
            .column-dir,
            .column-top,
            .column-empty,
            .column-top-30,
            .column-top-60,
            .column-empty2,
            .column-bottom {
                float: left !important;
                width: 100% !important;
                display: block !important;
            }

            .column-empty {
                padding-bottom: 15px !important;
            }

            .column-empty2 {
                padding-bottom: 30px !important;
            }

            .content-spacing {
                width: 15px !important;
            }
            .h2-center,
            h2,
            h2 span
            h3 {

                font-size: 18px !important;
                margin-top: 0 !important;
            }
            p, span {
                font-size: 10px !important;
            }
        }
    </style>
</head>

<body class="body" style="padding:0 !important; margin:0 !important; display:block !important; min-width:100% !important; width:100% !important; background:#d9d9d9; -webkit-text-size-adjust:none;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td align="center" valign="top">
                <!-- Main -->
                <table style="width:100%; max-width:650px; padding: 14px;" border="0" cellspacing="0" cellpadding="0" class="mobile-shell">
                    <tr>
                        <td class="td" style="width:100%; max-width:650px; font-size:0pt; line-height:0pt; padding:0; margin:0; font-weight:normal;">
                            <!-- Header -->
                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td class="p30-15" style="padding: 40px 0px 20px 0px;">
                                    </td>
                                </tr>
                                <!-- Logo -->
                                <tr>
                                    <td bgcolor="#ffffff" class="p30-15 img-center" style="padding: 30px; border-radius: 20px 20px 0px 0px; text-align:center;">
                                        @if(isset($companyLogo) &&  $companyLogo !== '' && $companyLogo !== null)
                                            <img src="{{$general->getFileUrl($companyLogo,'profile')}}"  border="0" alt="" style="width: 110px;" />
                                        @else
                                            <img src="{{$general->getFileUrl(config('setting.app_logo'),'setting')}}"  border="0" alt="" style="height: auto;" />
                                        @endif
                                    </td>
                                </tr>
                                <!-- END Logo -->
                                <!-- Nav -->
                                <tr>
                                    <td  bgcolor="#435ebe" class="text-nav-white bg-primary" style="color:#85b33a;font-size:12px; line-height:22px; text-align:center; text-transform:uppercase; padding:3px 0px;">
                                    </td>
                                </tr>
                                <!-- END Nav -->
                            </table>
                            <!-- END Header -->
                            @yield('content')
                            <!-- Footer -->
                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td class="p30-15-0" bgcolor="#435ebe" style="border-radius: 0px 0px 20px 20px; padding: 0; ">
                                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td align="center" class="p30-15" style="border-top: 1px solid #ebebeb; padding: 15px;">
                                                    <table class="center" border="0" cellspacing="0" cellpadding="0" style="text-align:center;">
                                                        <tr>
                                                            <td class="text-center" style="color:#ffffff; font-size:14px; line-height:22px; text-align:center; ">&copy; {{config('app.APP_NAME')}} {{date('Y')}}. All rights reserved.</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            <!-- END Footer -->
                        </td>
                    </tr>
                    <tr>
                        <td class="p30-15" style="padding: 40px 0px 20px 0px;">
                        </td>
                    </tr>
                </table>
                <!-- END Main -->
            </td>
        </tr>
    </table>
</body>
</html>