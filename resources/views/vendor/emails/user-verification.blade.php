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
	  padding: 20px;
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
        style="width: 200px; margin-top: 10px;"
    />

    <div style="margin: 20px">
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
            <h3>Hi {{$user->display_name}},</h3>
            <p style="margin-bottom: 30px">
                Thank you for signing up with Recruited!.
            </p>
            <p style="font-size: 14px; color: #4d4949">
                To ensure the security of
                your account, we need to verify your email address. Please click on
                the button below to confirm your email.
            </p>
            <br />
            <div style="height: 35px;">
                <a
                    href="{{ $actionUrl }}"
                    style="
              background-color: black;
              color: white;
              border: none;
              border-radius: 7px;
              text-align: center;
              padding-left: 20px;
              padding-right: 20px;
              padding-top: 10px;
              padding-bottom: 10px;
              text-decoration: none;
              margin-bottom: 32px;
              font-size: 14px;
            "
                >
                    {{ $actionText }}
                </a>
            </div>

            <p style="font-size: 14px; color: #4d4949">
                If you did not sign up for an account with Recruited, please
                disregard this email.
            </p>

            <p>
                Best  regards,
            </p>
            <p><b>Recruited</b></p>

            <hr />

            <p style="font-size: 14px; color: #4d4949">
                If you're having trouble clicking the "Verify Email Address" button,
                copy and paste the URL below into your web browser:
            </p>
            <a
                href="{{$actionUrl}}"
                style="font-size: 14px; color: blue"
            >
                {{$actionUrl}}
            </a>
        </div>
    </div>
</div>
<p style="font-size: 14px; text-align: center; margin-bottom: 15px; padding-bottom: 10px;">
    © {{ date('Y') }} Recruited. All rights reserved.
</p>
<br>
</body>
</html>
