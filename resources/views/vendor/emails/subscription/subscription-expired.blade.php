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

        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width: 90px; color: #ff9720;">
          <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003ZM12 8.25a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-1.5 0V9a.75.75 0 0 1 .75-.75Zm0 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd" />
        </svg>

        <h3> Your Subscription Has Expired </h3>
        <p style="font-size: 14px; color: #4d4949;"><b>Hi {{$user->display_name}}</b></p>
        <p style="font-size: 16px; color: #4d4949;">We wanted to let you know that your subscription to recruited has officially expired as of {{$expiration_date}}. This means you no longer have access to recruited.</p>
        
        <p style="font-size: 14px; color: #4d4949;"><b>Final billing details </b></p>
        <p style="font-size: 14px; color: #4d4949;">Last Billing Date: {{$last_billing_date}}</p>
        <p style="font-size: 14px; color: #4d4949;">Amount Paid: {{$amount_paid}}</p>

        <p style="font-size: 12px; color: #4d4949;">If you need assistance or believe this message was sent in error, feel free to reach out to us.</p>
      </div>
      
    </div>

    <p  style="font-size: 14px; text-align: center;"> © {{ date('Y') }} Recruited. All rights reserved.</p>
  </body>
</html>
