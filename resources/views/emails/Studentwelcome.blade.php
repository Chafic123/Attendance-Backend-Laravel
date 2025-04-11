<!DOCTYPE html>
<html>
<head>
    <title>University Credentials</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #f8f9fa; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .credentials { background-color: #f1f1f1; padding: 15px; border-radius: 5px; }
        .footer { margin-top: 20px; font-size: 0.9em; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to the University!</h1>
        </div>
        
        <div class="content">
            <p>Dear {{ $firstName }},</p>
            
            <p>Your university account has been created successfully. Here are your credentials:</p>
            
            <div class="credentials">
                <p><strong>Student ID (Login ID):</strong> {{ $studentId }}</p>
                <p><strong>University Email:</strong> {{ $studentEmail }}</p>
                <p><strong>Password:</strong> {{ $password }}</p>
                {{-- department + major  --}}
            </div>
            <p>If you didn't request this account, please contact the administration.</p>
        </div>
        
        <div class="footer">
            <p>Best regards,</p>
            <p>University Administration</p>
        </div>
    </div>
</body>
</html>