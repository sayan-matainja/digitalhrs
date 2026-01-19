<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Training Invitation</title>
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
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            background: #007bff;
            color: #ffffff;
            padding: 15px;
            border-radius: 8px 8px 0 0;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        .content {
            padding: 20px;
            color: #333;
        }
        .content p {
            line-height: 1.6;
        }
        .details {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .footer {
            text-align: center;
            font-size: 14px;
            color: #666;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h2>Invitation to Conduct Training</h2>
    </div>
    <div class="content">
        <p>Dear <strong>{{ $trainer->name }}</strong>,</p>
        <p>We are pleased to invite you to conduct a training session on <strong>{{ $topic }}</strong> for our team at <strong>{{ $company->name }}</strong>.</p>

        <div class="details">
            <p><strong>Date:</strong> {{ isset($training->end_date) ?  \App\Helpers\AppHelper::formatDateForView($training->start_date). ' - '.\App\Helpers\AppHelper::formatDateForView($training->end_date) :  \App\Helpers\AppHelper::formatDateForView($training->start_date) }}</p>
            <p><strong>Time:</strong> {{ \App\Helpers\AppHelper::convertLeaveTimeFormat($training->start_time) . ' - ' . \App\Helpers\AppHelper::convertLeaveTimeFormat($training->end_time) }}</p>
            <p><strong>Venue:</strong> {{ $training->venue ?? $company->address }}</p>
        </div>

        <p>Your expertise in <strong>{{ $trainer->expertise }}</strong> would be invaluable in equipping our participants with the necessary skills and knowledge.</p>
        <p>Please let us know your availability and if you require any specific arrangements. We would be honored to have you as our trainer and look forward to your positive response.</p>

        <p>Best regards,<br>
            <strong>{{ $company->name }}</strong><br>
            <a href="mailto:{{ $company->email }}">{{ $company->email }}</a> | {{ $company->phone }}</p>
    </div>
    <div class="footer">
        <p>This is an automated message, please do not reply directly to this email.</p>
        <p>&copy; {{ date('Y') }} {{ $company->name }}. All Rights Reserved.</p>
    </div>
</div>
</body>
</html>
