<!-- <!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>s</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      margin: 0;
      padding: 0;
    }

    .container {
      max-width: 600px;
      margin: 20px auto;
      background-color: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    h2 {
      color: #333;
    }

    p {
      color: #666;
      line-height: 1.6;
    }

    .credentials {
      margin-top: 20px;
      padding: 10px;
      background-color: #f9f9f9;
      border-radius: 4px;
      font-size: 14px;
    }

    .footer {
      margin-top: 20px;
      color: #888;
      font-size: 14px;
    }
  </style>
</head>


<body>
<div class="container">
    <h1>Enquiry Mail</h1>
    <p>New enquiry is received from the patient {{ $patient['first_name'] }} ! Here are the details:</p>

    <div class="credentials">
    <ul>
        <li><strong>Appointment Date:</strong> {{ $enquiry['date'] ?? 'N/A' }}</li>
        <li><strong>Patient name :</strong> {{ $patient['surname'] }} . {{ $patient['first_name'] }}</li>
        <li><strong>Patient Contact Number:</strong> {{ $patient['phone'] }}</li>

        <li><strong>Enquiry :</strong> {{ $enquiry['enquiry'] ?? 'N/A' }}</li>
        @if(isset($enquiry['created_at']))
        @php
        $dateTimeParts = explode(' ', $enquiry['created_at']);
        $date = $dateTimeParts[0] ?? 'N/A';
        $time = $dateTimeParts[1] ?? 'N/A';
        @endphp
        <li><strong>Mail Sent Date :</strong> {{ $date }}</li>
        <li><strong>Mail Sent Time :</strong> {{ $time }}</li>
        @else
        <li><strong>Mail Sent Date :</strong> N/A</li>
        <li><strong>Mail Sent Time :</strong> N/A</li>
        @endif
    </ul>
    </div>

    <div class="footer">
      <p>
        Best regards,<br />
        The Kirthika Dental Care
      </p>
    </div>
    </div>
</body>

</html> -->

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Enquiry Mail</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      margin: 0;
      padding: 20px;
    }

    .container {
      max-width: 600px;
      margin: auto;
      background-color: #ffffff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    h1 {
      font-size: 22px;
      color: #2c3e50;
      margin-bottom: 20px;
    }

    p {
      font-size: 16px;
      color: #555;
      margin-bottom: 20px;
    }

    ul {
      list-style-type: none;
      padding: 0;
      margin: 0;
    }

    li {
      padding: 8px 0;
      border-bottom: 1px solid #eee;
      font-size: 15px;
      color: #333;
    }

    li strong {
      width: 160px;
      display: inline-block;
      color: #2c3e50;
    }

    .footer {
      margin-top: 30px;
      font-size: 14px;
      color: #999;
      text-align: left;
      border-top: 1px solid #e0e0e0;
      padding-top: 15px;
    }
  </style>
</head>

<body>
  <div class="container">
    <p>
      A new enquiry has been received from the patient <strong>{{ $patient['first_name'] }} {{ $patient['surname'] }}</strong>.
      Here are the details:
    </p>

    <ul>
      <li><strong>Appointment Date:</strong> {{ $enquiry['date'] ?? 'N/A' }}</li>
      <li><strong>Patient Name:</strong> {{ $patient['first_name'] }} {{ $patient['surname'] }}</li>
      <li><strong>Contact Number:</strong> {{ $patient['phone'] ?? 'N/A' }}</li>
      <li><strong>Enquiry:</strong> {{ $enquiry['enquiry'] ?? 'N/A' }}</li>

      @if(isset($enquiry['created_at']))
        @php
          $dateTimeParts = explode(' ', $enquiry['created_at']);
          $date = $dateTimeParts[0] ?? 'N/A';
          $time = $dateTimeParts[1] ?? 'N/A';
        @endphp
        <li><strong>Mail Sent Date:</strong> {{ $date }}</li>
        <li><strong>Mail Sent Time:</strong> {{ $time }}</li>
      @else
        <li><strong>Mail Sent Date:</strong> N/A</li>
        <li><strong>Mail Sent Time:</strong> N/A</li>
      @endif
    </ul>

    <div class="footer">
      <p>
        Best regards,<br />
        <strong>{{ $patient['first_name'] }} {{ $patient['surname'] }}</strong>
      </p>
    </div>
  </div>
</body>

</html>
