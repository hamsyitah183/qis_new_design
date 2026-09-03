<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('New Application Submitted') }}</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f8f9fa;">

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f8f9fa; min-height: 100vh;">
    <tr>
        <td align="center" style="padding: 40px 20px;">
            <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; width: 100%;">

                {{-- Logo --}}
                <tr>
                    <td align="center" style="padding-bottom: 30px;">
                        @if(file_exists(public_path('images/Logo-DOA.png')))
                            <img src="{{ $message->embed(public_path('images/Logo-DOA.png')) }}" alt="DOA Logo" style="height: 60px; display: block;">
                        @else
                            <p style="font-size: 18px; font-weight: bold; color: #2e7d32;">QIS System</p>
                        @endif
                    </td>
                </tr>

                {{-- Card --}}
                <tr>
                    <td>
                        <table width="100%" cellpadding="0" cellspacing="0" border="0"
                               style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                            <tr>
                                <td style="padding: 40px 50px;">

                                    {{-- Label --}}
                                    <table cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 12px;">
                                        <tr>
                                            <td style="background-color: #e8f5e9; border-radius: 4px; padding: 4px 10px;">
                                                <span style="font-size: 11px; font-weight: 700; color: #2e7d32; text-transform: uppercase; letter-spacing: 1px;">
                                                    &#9679; APPLICATION
                                                </span>
                                            </td>
                                        </tr>
                                    </table>

                                    {{-- Title --}}
                                    <h1 style="margin: 0 0 20px 0; font-size: 22px; font-weight: 700; color: #1a1a1a;">
                                        {{ __('New Application Submitted') }}
                                    </h1>

                                    {{-- Divider --}}
                                    <hr style="border: none; border-top: 1px solid #eeeeee; margin: 0 0 20px 0;">

                                    {{-- Content --}}
                                    <div style="font-size: 14px; line-height: 1.8; color: #444444;">
                                        <p style="margin-top: 0;">{{ $messageEn }}</p>
                                        
                                        <p>
                                            <strong>{{ __('Application ID:') }}</strong> {{ $application_id }}<br>
                                            <strong>{{ __('Submitted By:') }}</strong> {{ $user_fullname }}
                                        </p>

                                        @if(!empty($appData))
                                        <div style="background-color: #f8f9fa; border: 1px solid #eeeeee; border-radius: 6px; padding: 20px; margin: 20px 0;">
                                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="color: #444444; font-size: 14px; line-height: 1.8;">
                                                <tr>
                                                    <td width="130" style="font-weight: 600;">Application Type:</td>
                                                    <td>
                                                        @if($appData['type'] == 0)
                                                            Self Import
                                                        @else
                                                            Import as Other
                                                        @endif

                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="font-weight: 600;">Category:</td>
                                                    <td>{{ $appData['category'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="font-weight: 600;">Importer:</td>
                                                    <td>{{ $appData['importer'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="font-weight: 600;">Transport Type:</td>
                                                    <td>{{ $appData['transport'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="font-weight: 600;">ETA:</td>
                                                    <td>{{ $appData['eta'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="font-weight: 600;">Current Status:</td>
                                                    <td style="color: #2e7d32; font-weight: 600;">{{ $appData['status'] }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                        @endif

                                        <p>{{ __('Please review the application details and choose an action below:') }}</p>
                                        
                                        <div style="margin-top: 24px; text-align: center;">
                                            <a href="{{ route('application.email.action', ['id' => $application_id, 'action' => 'approve']) }}" style="display: inline-block; background-color: #2e7d32; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; margin-right: 10px;">{{ __('Approve Application') }}</a>
                                            <a href="{{ route('application.email.action', ['id' => $application_id, 'action' => 'reject']) }}" style="display: inline-block; background-color: #dc3545; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: 600;">{{ __('Reject Application') }}</a>
                                        </div>
                                        
                                        <p style="margin-top: 24px;">
                                            {{ __('Or you can') }} <a href="{{ $url }}" style="color: #2e7d32; text-decoration: none; font-weight: 600;">{{ __('view the application here') }}</a>.
                                        </p>
                                    </div>

                                    {{-- Signature --}}
                                    <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eeeeee;">
                                        <p style="margin: 0; font-size: 14px; color: #6c757d;">Regards,</p>
                                        <p style="margin: 4px 0 0 0; font-size: 14px; font-weight: 600; color: #333333;">QIS System &#8212; Jabatan Pertanian Sabah</p>
                                    </div>

                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td align="center" style="padding-top: 20px;">
                        <p style="margin: 0; font-size: 11px; color: #aaaaaa;">
                            &#169; {{ date('Y') }} QIS. All rights reserved.<br>
                            This email was sent because you are a registered user of the QIS system.
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>
