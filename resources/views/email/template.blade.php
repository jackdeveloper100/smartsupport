@extends('email.layouts.main')
@section('content')

<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ebebeb">
    <tr>
        <td align="center">
            <table width="600" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff" style="padding: 55px 25px; width:100%">
                <!-- <tr>
                    <td align="center" style="padding: 20px;">
                        <i class="fas fa-lock" style="font-size: 50px; color: #00466a;"></i>
                    </td>
                </tr> -->
                <tr>
                    <td align="center" style="font-size: 24px; font-weight: bold; color: #333;">
                    </td>
                </tr>
                <tr>
                    <td align="center" style="font-size: 18px; color: #555; padding: 5px 0; line-height:normal;">
                        {!! $body !!}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

@endsection