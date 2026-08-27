@extends('email.layouts.main')
@section('content')
<!--<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ebebeb">-->
<!--    <tr>-->
<!--        <td class="p30-15-0" style="padding: 50px 30px 0px;" bgcolor="#ffffff">-->
<!--            <table width="100%" border="0" cellspacing="0" cellpadding="0">-->
<!--                <tr>-->
<!--                    <td class="h5-center" style="color:#a1a1a1; font-size:16px; line-height:22px; text-align:center; padding-bottom:5px;"></td>-->
<!--                </tr>-->
<!--                <tr>-->
<!--                    <td class="h2-center" style="color:#000000; font-size:32px; line-height:36px; text-align:center; padding-bottom:20px;">OTP</td>-->
<!--                </tr>-->
<!--                <tr>-->
<!--                    <td class="text-center" style="color:#5d5c5c; font-size:14px; line-height:22px; text-align:center; padding-bottom:22px;">Use this OTP to authenticate</td>-->
<!--                </tr>-->
<!--                <tr>-->
<!--                    <td align="center">-->
<!--                        <table border="0" cellspacing="0" cellpadding="0">-->
<!--                            <tr>-->
<!--                                <td class="text-button-orange bg-primary" style="color:#ffffff; font-size:14px; line-height:18px; text-align:center; padding:10px 30px; border-radius:20px;">-->
<!--                                    <h3>You have received OTP: {{$otp}}</h3>-->
<!--                                </td>-->
<!--                            </tr>-->
<!--                        </table>-->
<!--                    </td>-->
<!--                </tr>-->
<!--            </table>-->
<!--        </td>-->
<!--    </tr>-->
<!--</table>-->

<table border="0" cellpadding="0" cellspacing="0" width="100%" style="table-layout:fixed;background-color:#f9f9f9" id="bodyTable">
    <tbody>
        <tr>
            <td style="padding-right:10px;padding-left:10px;" align="center" valign="top" id="bodyCell">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" class="wrapperBody" style="max-width:600px;">
                    <tbody>
                        <tr>
                            <td align="center" valign="top">
                                <table border="0" cellpadding="0" cellspacing="0" width="100%" class="tableCard" style="background-color:#fff;border-color:#e5e5e5;border-style:solid;border-width:0 1px 1px 1px;">
                                    <tbody>
                                        <tr>
                                            <td style="background-color:#ff4800;font-size:1px;line-height:3px" class="topBorder" height="5">&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td style="padding-top: 60px; padding-bottom: 20px;" align="center" valign="middle" class="emailLogo">
                                                <a href="#" style="text-decoration:none" target="_blank">
                                                    <img alt="" border="0" src="https://cdn.prod.website-files.com/6285dac1a83c4a5e466613be/6285dac1a83c4ac8b56615ee_payoneer-logo-dark.svg" style="width:100%;max-width:150px;height:auto;display:block" width="100">
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding-bottom: 20px;">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding-bottom: 5px; padding-left: 20px; padding-right: 20px;" align="center" valign="top" class="mainTitle">
                                                <h2 class="text" style="color:#000;font-family:Poppins,Helvetica,Arial,sans-serif;font-size:28px;font-weight:500;font-style:normal;letter-spacing:normal;line-height:36px;text-transform:none;text-align:center;padding:0;margin:0">OTP</h2>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding-bottom: 30px; padding-left: 20px; padding-right: 20px;" align="center" valign="top" class="subTitle">
                                                <h4 class="text" style="color:#999;font-family:Poppins,Helvetica,Arial,sans-serif;font-size:16px;font-weight:500;font-style:normal;letter-spacing:normal;line-height:24px;text-transform:none;text-align:center;padding:0;margin:0">Use this OTP to authenticate</h4>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding-left:20px;padding-right:20px" align="center" valign="top" class="containtTable ui-sortable">
                                                <table border="0" cellpadding="0" cellspacing="0" width="100%" class="tableDescription" style="">
                                                    <tbody>
                                                        <tr>
                                                            <td style="padding-bottom: 20px;" align="center" valign="top" class="description">
                                                                <p class="text" style="color:#666;font-family:'Open Sans',Helvetica,Arial,sans-serif;font-size:14px;font-weight:400;font-style:normal;letter-spacing:normal;line-height:22px;text-transform:none;text-align:left;padding:0;margin:0">
                                                                    <strong>You have received OTP:</strong> {{$otp}}<br>
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
												<table border="0" cellpadding="0" cellspacing="0" width="100%" class="tableDescription" style="">
													<tbody>
														<tr>
															<td style="padding-bottom: 20px;" align="center" valign="top" class="description">
																<p class="text" style="color:#000;font-family:'Open Sans',Helvetica,Arial,sans-serif;font-size:14px;font-weight:400;font-style:normal;letter-spacing:normal;line-height:22px;text-transform:none;text-align:center;padding:0;margin:0">Thank You!</p>
															</td>
														</tr>
													</tbody>
												</table>
												
											</td>
										</tr>
                                        <tr>
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
