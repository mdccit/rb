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
    <title>Subscription Canceled</title>
    <style>
        h1 {
            display: block;
            font-size: 2em;
            margin-block-start: 0.67em;
            margin-block-end: 0.67em;
            font-weight: bold;
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
        src="https://recruited.pro/static/logo-black.png"
        alt="Recruited Logo"
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
            <p style="font-size: 14px; color: #4d4949;">
                We are sorry to see you go! Your subscription has been successfully canceled. You will continue to have access to your subscription until {{$subscription->end_date}}.
            </p>
            <p style="font-size: 14px; color: #4d4949;">
                If you have any questions or need further assistance, feel free to contact our support team.
            </p>
            <br>
            <p style="font-size: 14px; color: #4d4949;">
                Thank you for being a valued member!
            </p>
        </div>
    </div>

    <p style="font-size: 14px; text-align: center;"> © {{ date('Y') }} Recruited. All rights reserved.</p>
</div>
</body>
</html>
