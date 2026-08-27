@extends('email.layouts.main')
@section('content')
<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ebebeb">
    <tr>
        <td class="p30-15-0" style="padding: 55px 25px;" bgcolor="#ffffff">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td class="h5-center" style="color:#a1a1a1;  font-size:16px; line-height:22px; text-align:center; padding-bottom:5px;"></td>
                </tr>
                <tr>
                    <td class="h2-center" style="color:#000000;  font-size:32px; line-height:36px; text-align:center;">Hello {{$user->company_name}}, Your Trail Period was Expired</td>
                </tr>
                <tr>
                    <td class="text-center" > <p style="color:#5d5c5c;  font-size:16px; line-height:22px; text-align:center; padding-top:9px; padding-bottom:18px; font-weight: 500;">We have received your request</p> </td>
                </tr>
                <tr>
                    <td align="center">
                        <table border="0" cellspacing="0" cellpadding="0">
                            <tr>
                            <td style="background:#eeefff; color:#ffffff; font-size:14px; line-height:18px; text-align:center; padding:15px 20px; border-radius:9px;">
                            <h3 style="color: #000000 !important;font-size:18px; margin:0; font-weight: 500;"><a class="noroute" href="{{ route('company/plan', ['type'=>'plan','token' => base64_encode($user->email)]) }}">Renew Plan</a>
 </h3>
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