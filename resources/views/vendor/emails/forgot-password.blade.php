<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Figtree:ital,wght@0,300..900;1,300..900&display=swap"
        rel="stylesheet"
    />

    <title></title>
    <style>
        h1 {
            display: block;
            font-size: 2em;
            margin-block-start: 0.67em;
            margin-block-end: 0.67em;
            margin-inline-start: 0px;
            margin-inline-end: 0px;
            font-weight: bold;
            unicode-bidi: isolate;
        }
    </style>
</head>

<body
    style="
      width: 600px;
      margin: auto;
      background-color: rgb(223, 233, 241);
      font-family: Figtree, sans-serif;
	  padding: 30px;
    "
>
<div
    style="
        width: 100%;
        justify-items: center;
        text-align: center;
        margin-top: 30px;
      "
>
    <img
        src="https://ci3.googleusercontent.com/meips/ADKq_NbVf3Ie0ahLBPf_ZQHizIMwn5bO-Jwjb5EKYLwIY_sF9YC0WT3PnOtnOY5fzizX-9Y2S3YM5n9FSEClp0y6yAyN=s0-d-e1-ft#https://recruited.pro/static/logo-black.png"
        alt="Recruited Logo"
        class="CToWUd"
        data-bit="iit"
        style="width: 200px;"
    />

    <div style="margin:20px">
        <div
            style="
          background-color: white;
          width: 450px;
          height: auto;
          margin: auto;
          border-radius: 8px;
          padding: 30px;
        "
        >
            <h3>Hello {{$user->display_name}},</h3>
            <p style="font-size: 14px; color: #4d4949;">We received a request to reset your password. Please use the OTP (One-Time Password) below to reset your password. This OTP is valid for the next 10 minutes.
            </p>
            <p style="font-size: 14px; color: #4d4949;"><b>Your OTP.</b></p>

            <p style="font-size: 35px; color: #000000; letter-spacing: 7px;"><b>
                    {{$password_reset->recovery_code}}
                </b></p>
            <br>
            <a
                href="{{config('app.frontend_url').'reset-password'}}"
                style="font-size: 14px; color: blue"
            >{{config('app.frontend_url').'reset-password'}}</a>

            <p  style="font-size: 12px; color: #4d4949;">
                If you did not request a password reset, please ignore this email or contact our support team immediately.
            </p>
        </div>

    </div>

    <p  style="font-size: 14px; text-align: center;"> © {{ date('Y') }} Recruited. All rights reserved.</p>
</div>
</body>
</html>
