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
        <td class="p30-15-0" style="padding: 50px 30px 0px;" bgcolor="#ffffff">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td class="h5-center" style="color:#a1a1a1;  font-size:16px; line-height:22px; text-align:center; padding-bottom:5px;"></td>
                </tr>
                <tr>
                    <td class="h2-center" style="color:#000000;  font-size:32px; line-height:36px; text-align:center; padding-bottom:20px;">Verify Email</td>
                </tr>
                <tr>
                    <td class="text-center" style="color:#5d5c5c;  font-size:14px; line-height:22px; text-align:center; padding-bottom:22px;">Use the link below to verify your email</td>
                </tr>
                <tr>
                    <td align="center">
                        <table border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td class="text-button-orange bg-primary" style=" color:#ffffff; font-size:14px; line-height:18px; text-align:center; padding:10px 30px; border-radius:20px;">
                                    <a href="{{$url}}" target="_blank" class="link-white" style="color:#ffffff; text-decoration:none;">
                                        <span class="link-white" style="color:#ffffff; text-decoration:none;">Verify</span>
                                    </a>
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