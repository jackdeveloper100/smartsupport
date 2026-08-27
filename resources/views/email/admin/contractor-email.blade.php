@extends('email.layouts.main')
@section('content')
<style>
    .h2{
        font-size:28px !important;
    }
    @media (max-width: 480px) {
        .h2{
            font-size: 22px !important;
        }
        
    }
</style>

<table border="0" cellpadding="0" cellspacing="0" width="100%" style="table-layout:fixed;background-color:#f9f9f9" id="bodyTable">
    <tbody>
        <tr>
            <td align="center" valign="top" id="bodyCell">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" class="wrapperBody" style="max-width:100%;">
                    <tbody>
                        <tr>
                            <td align="center" valign="top">
                                <table border="0" cellpadding="0" cellspacing="0" width="100%" class="tableCard" style="background-color:#fff;border-color:#e5e5e5;border-style:soli;border-width:0 1px 0px 1px; padding: 55px 25px;">
                                    <tbody>
                                      
                                       
                                        <!-- <tr>
                                            <td style="padding-bottom: 20px;">
                                            </td>
                                        </tr> -->
                                        <tr>
                                            <td style="padding-bottom: 5px; padding-left: 20px; padding-right: 20px;" align="center" valign="top" class="mainTitle">
                                                <h2 class="h2 text" style="color:#000;font-family:Poppins,Helvetica,Arial,sans-serif;font-weight:500;font-size:28px;line-height:36px;text-transform:none;text-align:center;padding:0;margin:8px 0;">{{$subject}}</h2>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td  align="center" valign="top" class="containtTable ui-sortable">
                                                <table border="0" cellpadding="0" cellspacing="0" width="100%" class="tableDescription">
                                                    <tbody>
                                                        <tr>
                                                            <td style="padding-bottom: 20px;" align="center" valign="top" class="description">
                                                                <p class="text" style="color:#666;font-family:'Open Sans',Helvetica,Arial,sans-serif;font-size:16px;font-weight:400;line-height:22px;text-transform:none;text-align:center;padding-top:5px !important;margin:0;">
                                        {{$message}}<br>
                                                                </p>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        <!-- <tr>
                                            <td style="font-size:1px;line-height:1px" height="20">&nbsp;</td>
                                        </tr> -->
                                        <tr>
                                            <td align="center" valign="top" class="containtTable ui-sortable">
                                                <table border="0" cellpadding="0" cellspacing="0" width="100%" class="tableDescription">
                                                    <tbody>
                                                        <tr>
                                                            <td align="center" valign="top" class="description">
                                                                <p class="text" style="color:#000;font-family:'Open Sans',Helvetica,Arial,sans-serif;font-size:16px;font-weight:400;line-height:22px;text-transform:none;text-align:center;padding:0;margin:0;">
                                                                    Thank You!
                                                                </p>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        <!-- <tr>
                                            <td style="font-size:1px;line-height:1px" height="20">&nbsp;</td>
                                        </tr> -->
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
