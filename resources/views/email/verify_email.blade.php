@extends('pages.front', ['title' => 'verify_email'])

@section('content')
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f8f9fa; min-height: 100vh;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; width: 100%;">
                    <!-- Logo -->
                    <tr>
                        <td align="center" style="padding-bottom: 30px;">
                            <img src="https://qis-dashboard.sabah.gov.my/assets/logo-small-2e441c05.png" alt= "Logo"
                                style="height: 50px; display: block;">
                        </td>
                    </tr>

                    <!-- Card -->
                    <tr>
                        <td>
                            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                                style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                                <tr>
                                    <td style="padding: 50px;">
                                        <!-- Title -->
                                        <h1 style="margin: 0 0 15px 0; font-size: 24px; font-weight: 600; color: #333333;">
                                            Welcome to QIS, please verify your account {{ $name ?? '' }}</h1>

                                        <!-- Message -->
                                        <p style="margin: 0 0 20px 0; font-size: 14px; line-height: 1.6; color: #6c757d;">
                                            Please click the button below to verify your email address.</p>

                                        <!-- Button -->
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td align="center" style="padding: 20px 0 30px 0;">
                                                    <a href="{{ $url ?? '#' }}"
                                                        style="display: inline-block; padding: 12px 35px; background-color: #212529; color: #ffffff; text-decoration: none; border-radius: 5px; font-weight: 600; font-size: 14px;">Verify
                                                        Email</a>
                                                </td>
                                            </tr>
                                        </table>

                                        <!-- Warning message -->
                                        <p style="margin: 0 0 30px 0; font-size: 14px; line-height: 1.6; color: #6c757d;">If
                                            you did not create an account, no further action is required.</p>

                                        <!-- Signature -->
                                        <div style="margin-bottom: 40px;">
                                            <p style="margin: 0; font-size: 14px; color: #6c757d;">Regards,</p>
                                            <p
                                                style="margin: 5px 0 0 0; font-size: 14px; font-weight: 600; color: #6c757d;">
                                                QIS System</p>
                                        </div>

                                        <!-- Divider -->
                                        <div style="border-top: 1px solid #e9ecef; padding-top: 25px;">
                                            <p style="margin: 0; font-size: 12px; line-height: 1.6; color: #6c757d;">
                                                If you're having trouble clicking the "Verify Email" button, copy and paste
                                                the URL below into your web browser:
                                                <br><br>
                                                <a href="{{ $url ?? '#' }}"
                                                    style="color: #0d6efd; word-break: break-all;">{{ $url ?? '#' }}</a>
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding-top: 20px;">
                            <p style="margin: 0; font-size: 11px; color: #6c757d;">© {{ date('Y') }} QIS. All rights
                                reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
@endsection