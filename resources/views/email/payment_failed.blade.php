@extends('pages.front', ['title' => 'payment_failed'])

@section('content')
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f8f9fa; min-height: 100vh;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; width: 100%;">
                    <!-- Logo -->
                    <tr>
                        <td align="center" style="padding-bottom: 30px;">
                            <img src="https://qis-dashboard.sabah.gov.my/assets/logo-small-2e441c05.png" alt="Logo"
                                style="height: 50px; display: block;">
                        </td>
                    </tr>

                    <!-- Card -->
                    <tr>
                        <td>
                            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                                style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                                <tr>
                                    <td style="padding: 50px 40px;">
                                        <!-- Failed Icon -->
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td align="center" style="padding-bottom: 20px;">
                                                    <table cellpadding="0" cellspacing="0" border="0"
                                                        style="width: 60px; height: 60px; background-color: #ef4444; border-radius: 50%; margin: 0 auto;">
                                                        <tr>
                                                            <td align="center" valign="middle"
                                                                style="text-align: center; vertical-align: middle; line-height: 60px;">
                                                                <span
                                                                    style="color: #ffffff; font-size: 40px; font-weight: bold; display: inline-block;">✕</span>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>

                                        <!-- Title -->
                                        <h1
                                            style="margin: 0 0 15px 0; font-size: 28px; font-weight: 600; color: #1f2937; text-align: center;">
                                            Payment Unsuccessful</h1>

                                        <!-- Order Message -->
                                        <p
                                            style="margin: 0 0 30px 0; font-size: 14px; line-height: 1.6; color: #6b7280; text-align: center;">
                                            Your order (<strong>{{ $orderNumber }}</strong>) payment was unsuccessful.
                                            Please try again.
                                        </p>

                                        <!-- Payment Details -->
                                        <div
                                            style="background-color: #fef2f2; border-radius: 6px; padding: 25px; margin-bottom: 20px; border-left: 4px solid #ef4444;">
                                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                                <!-- FPX Reference -->
                                                <tr>
                                                    <td style="padding: 8px 0;">
                                                        <p
                                                            style="margin: 0; font-size: 14px; color: #1f2937; font-weight: 600;">
                                                            FPX Reference:</p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 0 0 15px 0;">
                                                        <p style="margin: 0; font-size: 14px; color: #6b7280;">
                                                            {{ $fpxReference }}
                                                        </p>
                                                    </td>
                                                </tr>

                                                <!-- Amount -->
                                                <tr>
                                                    <td style="padding: 8px 0;">
                                                        <p
                                                            style="margin: 0; font-size: 14px; color: #1f2937; font-weight: 600;">
                                                            Amount:</p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 0 0 15px 0;">
                                                        <p style="margin: 0; font-size: 14px; color: #6b7280;">
                                                            RM {{ number_format($amount, 2) }}</p>
                                                    </td>
                                                </tr>

                                                <!-- Application Type -->
                                                <tr>
                                                    <td style="padding: 8px 0;">
                                                        <p
                                                            style="margin: 0; font-size: 14px; color: #1f2937; font-weight: 600;">
                                                            Application Type:</p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 0 0 15px 0;">
                                                        <p style="margin: 0; font-size: 14px; color: #6b7280;">
                                                            {{ $applicationType }}
                                                        </p>
                                                    </td>
                                                </tr>

                                                <!-- Name -->
                                                <tr>
                                                    <td style="padding: 8px 0;">
                                                        <p
                                                            style="margin: 0; font-size: 14px; color: #1f2937; font-weight: 600;">
                                                            Name:</p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 0 0 15px 0;">
                                                        <p style="margin: 0; font-size: 14px; color: #6b7280;">
                                                            {{ $customerName }}
                                                        </p>
                                                    </td>
                                                </tr>

                                                <!-- Permits Number -->
                                                <tr>
                                                    <td style="padding: 8px 0;">
                                                        <p
                                                            style="margin: 0; font-size: 14px; color: #1f2937; font-weight: 600;">
                                                            Permit{{ count($permitNumbers) > 1 ? 's' : '' }} Number:</p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 0 0 5px 0;">
                                                        @foreach ($permitNumbers as $permitNumber)
                                                            <p style="margin: 0 0 5px 0; font-size: 14px; color: #6b7280;">
                                                                {{ $permitNumber }}
                                                            </p>
                                                        @endforeach
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>

                                        <!-- Info Box -->
                                        <div
                                            style="background-color: #eff6ff; border-radius: 6px; padding: 20px; margin-bottom: 20px; border-left: 4px solid #3b82f6;">
                                            <p style="margin: 0; font-size: 14px; line-height: 1.6; color: #1e40af;">
                                                <strong>What to do next:</strong><br>
                                                • Check your bank account balance<br>
                                                • Ensure your payment details are correct<br>
                                                • Try again using a different payment method<br>
                                                • Contact your bank if the problem persists
                                            </p>
                                        </div>

                                        <!-- Retry Button -->
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td align="center" style="padding: 20px 0;">
                                                    <a href="{{ url('/') }}"
                                                        style="display: inline-block; padding: 12px 35px; background-color: #ef4444; color: #ffffff; text-decoration: none; border-radius: 5px; font-weight: 600; font-size: 14px;">
                                                        Try Payment Again
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>

                                        <!-- Signature -->
                                        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                                            <p style="margin: 0; font-size: 14px; color: #6b7280;">If you need assistance,
                                                please contact our support team.</p>
                                            <p
                                                style="margin: 5px 0 0 0; font-size: 14px; font-weight: 600; color: #1f2937;">
                                                QIS System</p>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding-top: 20px;">
                            <p style="margin: 0; font-size: 11px; color: #6b7280;">© {{ date('Y') }} QIS. All rights
                                reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
@endsection