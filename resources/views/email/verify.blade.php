<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Verify Your Email Address') }}</title>
    <style>
        @import"https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap";

        /* Reset and base – matching QIS front-end */
        body, table, td, a {
            margin: 0;
            padding: 0;
            border: 0;
            font-family:  "Poppins", sans-serif;
            color: #1a2b4a;
        }
        table { border-collapse: collapse; width: 100%; }
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background: #f8f9fa;
            padding: 20px;
        }
        .email-content {
            background: #ffffff;
            border-radius: 16px;
            padding: 40px 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .email-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .email-header img {
            max-height: 50px;
            width: auto;
        }
        .email-header h2 {
            margin: 10px 0 0;
            font-weight: 300;
            color: #1a2b4a;
            font-size: 22px;
        }
        .email-body h1 {
            font-size: 24px;
            font-weight: 600;
            color: #1a2b4a;
            margin-top: 0;
        }
        .btn-auth-primary {
            display: inline-block;
            background: rgb(45, 143, 79);            /* primary color from your app */
            color: #ffffff !important;
            text-decoration: none;
            padding: 10px 22px;
            border-radius: 12px;
            font-weight: 600;
            margin: 20px 0;
            transition: background 0.2s;
            border: none;
        }
        .btn-auth-primary:hover {
            background: rgb(35, 114, 63);
        }
        .email-footer {
            margin-top: 30px;
            font-size: 13px;
            color: #888;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        .text-muted {
            color: #6c757d;
        }
        @media only screen and (max-width: 480px) {
            .email-content { padding: 20px 15px; }
            .btn-auth-primary { display: block; text-align: center; }
        }
    </style>
</head>
<body>
    <table class="email-wrapper">
        <tr>
            <td align="center">
                <table class="email-content">
                    <tr>
                        <td>
                            <!-- Header with logo -->
                            <div class="email-header">
                                <img src="{{ asset('images/Logo_DOA.png') }}" alt="{{ config('app.name') }} Logo">
                                <h2>{{ config('app.name') }}</h2>
                            </div>

                            <!-- Body -->
                            <div class="email-body">
                                <h1>{{ __('Welcome to Quarantine Information System') }}{{ $user->fullname ? ', ' . $user->fullname : '' }}</h1>

                                <p>{{ __('Please click the button below to verify your email address.') }}</p>

                                <p style="text-align: center;">
                                    <a href="{{ $url }}" class="btn-auth-primary">{{ __('Verify Email') }}</a>
                                </p>

                                <p>{{ __('If you did not create an account, no further action is required.') }}</p>

                                <p class="text-muted" style="font-size: 14px;">
                                    {{ __('This link will expire in 60 minutes.') }}
                                </p>
                            </div>

                            <!-- Footer -->
                            <div class="email-footer">
                                <p>
                                    {{ __('© :year :app. All rights reserved.', ['year' => now()->year, 'app' => config('app.name')]) }}
                                </p>
                                <p>
                                    <small>{{ __('This is an automated message, please do not reply.') }}</small>
                                </p>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>