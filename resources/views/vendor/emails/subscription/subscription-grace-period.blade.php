{{-- resources/views/vendor/emails/subscription/subscription-grace-period.blade.php --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Subscription is in Grace Period</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f7;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: none;
            width: 100% !important;
        }
        .email-container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 3px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333333;
            font-size: 24px;
        }
        p {
            color: #555555;
            font-size: 16px;
            line-height: 1.5;
        }
        a {
            color: #007BFF;
            text-decoration: none;
        }
        .btn {
            background-color: #007BFF;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        .footer {
            text-align: center;
            padding-top: 20px;
            font-size: 12px;
            color: #aaaaaa;
        }
        .email-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .email-header img {
            max-width: 150px;
        }
    </style>
</head>
<body>
    <div class="email-container">
       <!-- Logo Section -->
       <div class="email-header">
       <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width: 90px; color: #ff9720;">
          <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003ZM12 8.25a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-1.5 0V9a.75.75 0 0 1 .75-.75Zm0 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd" />
      </svg>
        </div>

        <h1>Hello {{ $user->name }},</h1>

        <p>We wanted to inform you that your subscription has expired, but we are giving you a grace period of 7 days.</p>
        
        <p>During this grace period, you will continue to have access to your subscription until <strong>{{ $grace_period_end->toFormattedDateString() }}</strong>.</p>

        <!-- <p>If you'd like to renew your subscription, please click the button below:</p>

        <a href="{{ url('/renew-subscription') }}" class="btn">Renew Subscription</a> -->

        <p>If you have any questions, feel free to contact our support team.</p>

        <p>Thank you for using our services!</p>

        <p>Best regards,<br> Recruited Team</p>

        <div class="footer">
            © {{ date('Y') }} Recruited. All rights reserved.
        </div>
    </div>
</body>
</html>
