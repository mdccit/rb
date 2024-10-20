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
		<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width: 90px; color: #ff9720;">
			<path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003ZM12 8.25a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-1.5 0V9a.75.75 0 0 1 .75-.75Zm0 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd" />
		</svg>

        <h3> Your Credit Card is About to Expire </h3>
		<p style="font-size: 14px; color: #4d4949;"><b>Hi {{$user->display_name}}</b></p>
        <p style="font-size: 16px; color: #4d4949;">We noticed that the credit card linked to your Recruited subscription is set to expire soon on {{$card_expiry_date}}. <br><br>
		To avoid any interruptions to your service, please update your payment method before your next billing cycle.
        </p>
		
		 <p style="font-size: 14px; color: #4d4949;"><b>How to update your payment details</b></p>
		<p style="font-size: 14px; color: #4d4949;">Log in to your account <span style="color: blue">Here</span></p>
		<p style="font-size: 14px; color: #4d4949;">Go to the Billing section.</p>
		<p style="font-size: 14px; color: #4d4949;">Update your credit card information.</p>
		<p style="font-size: 14px; color: #4d4949;">By ensuring your payment details are up to date, you can continue enjoying uninterrupted access to Recruited.</p>
      <br>
        <p  style="font-size: 12px; color: #4d4949;">
         If you have any questions or need assistance, feel free to contact us.
        </p>
      </div>
    </div>

    <p  style="font-size: 14px; text-align: center;"> © {{ date('Y') }} Recruited. All rights reserved.</p>
  </body>
</html>
