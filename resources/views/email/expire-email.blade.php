@extends('email.layouts.main')
@section('content')
<style>
   @media (max-width: 576px) {

             .h2-center,
             td.h2-center 
             h2, 
             h2 span
             h3 {
                font-size: 18px !important;
            } 
    }
</style>
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="table-layout:fixed;background-color:#f9f9f9" id="bodyTable">
    <tbody>
        <tr>
            <td style="padding-right:10px;padding-left:10px;" align="center" valign="top" id="bodyCell">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" class="wrapperBody" style="max-width:600px;">
                    <tbody>
                        <tr>
                            <td align="center" valign="top">
                                <table border="0" cellpadding="0" cellspacing="0" width="100%" class="tableCard" style="background-color:#fff;border-color:#e5e5e5;border-style:solid;border-width:0 1px 1px 1px; padding: 20px;">
                                    <tbody>
                                        <tr>
                                            <td style="background-color:#ff4800;font-size:1px;line-height:3px" class="topBorder" height="5">&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td style="padding-top: 60px; padding-bottom: 20px;" align="center" valign="middle" class="emailLogo">
                                                
                                                    <img src="{{$general->getFileUrl(config('setting.app_logo'),'setting')}}" height="50" border="0" alt="Logo" />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding-bottom: 20px;">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding-bottom: 5px;" align="center" valign="top" class="mainTitle">
                                                <h2 class="text" style="color:#000;font-family:Poppins,Helvetica,Arial,sans-serif;font-size:28px;font-weight:500;line-height:36px;text-transform:none;text-align:center;padding:0;margin:0;">Reminder Of Your Document</h2>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding-left:20px;padding-right:20px" align="center" valign="top" class="containtTable ui-sortable">
                                                <table border="0" cellpadding="0" cellspacing="0" width="100%" class="tableDescription">
                                                    <tbody>
                                                        <tr>
                                                            <td style="padding-bottom: 20px;" align="center" valign="top" class="description">
                                                                <p class="text" style="color:#666;font-family:'Open Sans',Helvetica,Arial,sans-serif;font-size:14px;font-weight:400;line-height:22px;text-transform:none;text-align:left;padding:0;margin:0;">
                                                                    <br>
                                                                    @if(@$expiringDescription)
                                                                    <strong>Hello {{$name}} {{$expiringDescription}}. To ensure continued compliance and avoid any disruptions to your operations, make sure to renew your business license before the expiration date
                                                                    </strong>
                                                                    @elseif(@$expiredDocument)
                                                                    <strong>Hello {{$name}} {{$expiredDocument}} and are now considered invalid. To avoid potential legal issues and ensure your business remains in good standing, it's important to renew or update these documents immediately.
                                                                    </strong>
                                                                    @elseif(@$description)
                                                                        @if($DescriptionType == 'approve')
                                                                        <strong>Hello {{$name}} {{$description}} has been approved. We are now moving forward with the necessary updates to ensure that all documents are current and valid.
                                                                        </strong>
                                                                        @else
                                                                        <strong>Hello {{$name}} {{$description}} has been Rejected.due to the documents being invalid. We are now taking the necessary steps to update and ensure that all documents are current and valid moving forward.
                                                                        </strong>
                                                                        @endif
                                                                    @endif
                                                                    <br>
                                                                </p>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="font-size:1px;line-height:1px" height="20">&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td style="padding-left:20px;padding-right:20px" align="center" valign="top" class="containtTable ui-sortable">
                                                <table border="0" cellpadding="0" cellspacing="0" width="100%" class="tableDescription">
                                                    <tbody>
                                                        <tr>
                                                            <td style="padding-bottom: 20px;" align="center" valign="top" class="description">
                                                                <p class="text" style="color:#000;font-family:'Open Sans',Helvetica,Arial,sans-serif;font-size:16px;font-weight:400;line-height:22px;text-transform:none;text-align:center;padding:0;margin:0;">
                                                                    Thank You!
                                                                </p>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="font-size:1px;line-height:1px" height="20">&nbsp;</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </tbody>
</table>
@endsection
