<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
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
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #333333;
        }
        p {
            color: #555555;
            line-height: 1.6;
        }
        .password {
            background-color: #eef;
            border: 1px solid #007BFF;
            padding: 10px;
            border-radius: 4px;
            font-weight: bold;
            color: #007BFF;
        }
        .footer {
            margin-top: 20px;
            color: #777777;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Hello {{ $name }},</h2>
        <p>Your new password is:</p>
        <p class="password">{{ $password }}</p>
        <p>Please log in and change your password immediately.</p>
        <div class="footer">
            <p>Best regards,</p>
            <p>Attendance System Team</p>
        </div>
    </div>
</body>
</html>