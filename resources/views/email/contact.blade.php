@extends('email.layouts.main')

@section('content')
<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#EBEBEB" style="padding:0; margin:0;">
    <tr>
        <td align="center" valign="top" style="padding:0; margin:0;">
            <table width="100%" border="0" cellspacing="0" cellpadding="0" class="email-container" style="max-width:100%; width:100%; background-color:#FFFFFF; margin:0 auto;">
                <tr>
                    <td align="center" valign="top" class="email-padding" style="padding:30px 20px; font-family:Arial, sans-serif;">
                        
                        <!-- Heading -->
                        <h2 class="email-heading" style="font-size:24px; color:#000000; margin:0; text-align:center; line-height:1.2;">Someone Contacted You</h2>
                        <p class="email-text" style="font-size:16px; color:#666666; text-align:center; margin:10px 0 25px 0; line-height:1.4;">We have received your request.</p>
                        
                        <!-- Info Box (everything inside!) -->
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color:#EEEFFF; border-radius:8px; padding:0;">
                            <tr>
                                <td style="padding:20px; font-family:Arial, sans-serif; font-size:14px; color:#000000; line-height:1.5; text-align:left;">
                                    <p style="margin:0 0 10px 0;"><strong>Name:</strong> {{ $postData['first_name'] ?? '' }} {{ $postData['last_name'] ?? '' }}</p>
                                    <p style="margin:0 0 10px 0;"><strong>Email:</strong> {{ $postData['email'] ?? '' }}</p>
                                    <p style="margin:0 0 10px 0;"><strong>Contact Number:</strong> {{ $postData['mobile_number'] ?? '' }}</p>
                                    <p style="margin:0;"><strong>Message:</strong> {{ $postData['message'] ?? '' }}</p>
                                </td>
                            </tr>
                        </table>

                        <!-- Spacer -->
                        <div style="height:40px; line-height:40px;">&nbsp;</div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endsection
