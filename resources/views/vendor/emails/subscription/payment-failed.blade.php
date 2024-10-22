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

    <title>Payment Failed</title>
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

        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-2" style="width: 90px; color: #f60808;">
          <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.72 6.97a.75.75 0 1 0-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 1 0 1.06 1.06L12 13.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L13.06 12l1.72-1.72a.75.75 0 1 0-1.06-1.06L12 10.94l-1.72-1.72Z" clip-rule="evenodd" />
        </svg>

        <h3>Payment Failed! <br> Action Required to Continue Your Subscription</h3>
        <p style="font-size: 14px; color: #4d4949;"><b>Hi {{$user->display_name}}</b></p>
        <p style="font-size: 16px; color: #4d4949;">We’re writing to inform you that unfortunately, your recent payment attempt for recruited was unsuccessful.</p>
        
        <p style="font-size: 14px; color: #4d4949;"><b>Reason for failure: </b> {{$failure_reason}}</p>
        <p style="font-size: 14px; color: #4d4949;"><b>Here are the details of the failed payment:</b></p>
        <p style="font-size: 14px; color: #4d4949;">Amount: {{$amount_due}}</p>
        <p style="font-size: 14px; color: #4d4949;">Payment Date: {{$payment_date}}</p>
      
        <br>
        <p style="font-size: 12px; color: #4d4949;">If you need assistance or believe this message was sent in error, feel free to reach out to us.</p>
      </div>
    </div>

    <p style="font-size: 14px; text-align: center;">© 2024 Recruited. All rights reserved.</p>
  </body>
</html>
