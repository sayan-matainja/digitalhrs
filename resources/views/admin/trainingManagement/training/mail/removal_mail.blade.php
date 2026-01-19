<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Training Removal Notification</title>
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
            background: #dc3545;
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
        <h2>Training Removal Notification</h2>
    </div>
    <div class="content">
        <p>Dear <strong>{{ $trainer->name }}</strong>,</p>
        <p>We regret to inform you that you have been removed from the training session on <strong>{{ $topic }}</strong> for our team at <strong>{{ $company->name }}</strong>.</p>

        <p>We sincerely appreciate your willingness to contribute and hope to collaborate with you in the future.</p>
        <p>If you have any questions or require further clarification, please do not hesitate to reach out.</p>

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
