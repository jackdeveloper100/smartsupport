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
<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ebebeb">
    <tr>
        <td class="p30-15-0" style="padding: 55px 25px; line-height: normal;" bgcolor="#ffffff">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td class="h5-center" style="color:#a1a1a1;  font-size:16px; line-height:22px; text-align:center; padding-bottom:5px;"></td>
                </tr>
                <tr>
                    <td class="h2-center" style="color:#999999;font-size:2rem; text-align:center;">Login With Diffrent Location</td>
                </tr>
                <tr>
                    <td class="text-center" style="color:#5d5c5c;  font-size:14px; line-height:22px; text-align:center; padding-top:8px; padding-bottom:20px;">Someone is loggged in from different location</td>
                </tr>
                <tr>
                    <td align="center">
                        <table border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td style="background:#eeefff; color:#ffffff; font-size:14px; line-height:18px; text-align:center; padding:20px 30px; border-radius:9px;">
                                    <h3 style="color: #000000 !important;font-size:18px; margin:0 0 10px 0; font-weight: 500;">Hi {{$name}} : You account is logged in from new device</h3>
                                    <p style="color: #000000 !important;font-size:14px; padding-bottom:4px !important;">Ip : {{$ip}}</p>
                                    <p style="color: #000000 !important;font-size:14px; padding-bottom:4px !important;">Device : {{$client}}</p>
                                    <p style="color: #000000 !important;font-size:14px; padding-bottom:4px !important;">Location : {{$ip_info}}</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endsection