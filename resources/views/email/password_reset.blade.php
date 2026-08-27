@extends('email.layouts.main')
@section('content')
<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ebebeb">
    <tr>
        <td class="p30-15-0" style="padding: 50px 30px 0px;" bgcolor="#ffffff">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td class="h5-center" style="color:#a1a1a1;  font-size:16px; line-height:22px; text-align:center; padding-bottom:5px;"></td>
                </tr>
                <tr>
                    <td class="h2-center" style="color:#000000;  font-size:32px; line-height:36px; text-align:center; padding-bottom:20px;">Forgot Password</td>
                </tr>
                <tr>
                    <td class="text-center" style="color:#5d5c5c;  font-size:14px; line-height:22px; text-align:center; padding-bottom:22px;">If you've lost your password or wish to reset it, use the link below to started it.</td>
                </tr>
                <tr>
                    <td align="center">
                        <table border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td class="text-button-orange bg-primary" style=" color:#ffffff; font-size:14px; line-height:18px; text-align:center; padding:10px 30px; border-radius:20px;">
                                    <a href="{{$url}}" target="_blank" class="link-white" style="color:#ffffff; text-decoration:none;">
                                        <span class="link-white" style="color:#ffffff; text-decoration:none;">Reset Your Password</span>
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