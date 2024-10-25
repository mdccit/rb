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
      <img
        src="https://i.postimg.cc/htQxP8TQ/icons8-correct-144-1.png"
        alt="Recruited Logo"
        data-bit="iit"
       style="width:120px"
      />
	  
        <h3>Payment Successful!  <br> Thank You for Your Purchase</h3>
		<p style="font-size: 14px; color: #4d4949;"><b>Hi {{ $display_name }} </b></p>
        <p style="font-size: 16px; color: #4d4949;">We’re happy to inform you that your payment was successfully processed! Thank you for choosing Recruited
        </p>
        <p style="font-size: 14px; color: #4d4949;"><b>Here are the details of your payment</b></p>
		<p style="font-size: 14px; color: #4d4949;">Amount Paid:  {{ $currency }} {{ number_format($amount, 2) }}</p>
		<p style="font-size: 14px; color: #4d4949;">Payment Date: {{ $subscription->start_date }}</p>
		<p style="font-size: 14px; color: #4d4949;">Transaction ID: 129-283</p>
      
        <br>
		
        <p  style="font-size: 12px; color: #4d4949;">
         Thank you for your continued trust in Recruited. We’re here to ensure you have the best experience possible.

			If you have any questions or need assistance with your account, feel free to contact us.
        </p>
      </div>
      
    </div>

    <p  style="font-size: 14px; text-align: center;"> © 2024 Recruited. All rights reserved.</p>
      </div>
  </body>
</html>
