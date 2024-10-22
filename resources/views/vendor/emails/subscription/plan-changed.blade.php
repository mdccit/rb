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

    <title>Subscription Plan Changed</title>
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

        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width: 90px; color: #81d400;">
          <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd"/>
        </svg>

        <h3> Subscription Plan Changed </h3>
        <p style="font-size: 14px; color: #4d4949;"><b>Hi {{$user->display_name}}</b></p>
        <p style="font-size: 16px; color: #4d4949;">We’re writing to confirm that your subscription plan has been successfully {{$plan_status}} to the {{$new_plan_name}}.</p>

        <p style="font-size: 14px; color: #4d4949;"><b>Here are the details of your new plan:</b></p>
        <p style="font-size: 14px; color: #4d4949;">New Plan: {{$new_plan_name}}</p>
        <p style="font-size: 14px; color: #4d4949;">Price: {{$new_plan_price}}</p>
        <p style="font-size: 14px; color: #4d4949;">Billing Cycle: {{$billing_cycle}}</p>
        <p style="font-size: 14px; color: #4d4949;">Next Charge Date: {{$next_billing_date}}</p>

        <p style="font-size: 12px; color: #4d4949;">If you have any questions or need assistance, feel free to contact us.</p>
      </div>
    </div>

    <p style="font-size: 14px; text-align: center;">© 2024 Recruited. All rights reserved.</p>
  </body>
</html>
