<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Employment Reference Request</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .button { display: inline-block; padding: 10px 20px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px; }
        .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Employment Reference Request</h1>
        </div>

        <div class="content">
            <p>Dear {{ $company->name }},</p>

            <p>We are conducting a background verification for one of our driver applicants and would appreciate your feedback regarding their employment with your company.</p>

            <p><strong>Driver Details:</strong></p>
            <ul>
                <li><strong>Name:</strong> {{ $driver->full_name }}</li>
                <li><strong>Driver ID:</strong> {{ $driver->driver_id }}</li>
                <li><strong>Email:</strong> {{ $driver->email }}</li>
                <li><strong>Phone:</strong> {{ $driver->phone }}</li>
            </ul>

            <p>Your feedback will help us ensure the safety and reliability of our transportation services. Please click the button below to provide your reference:</p>

            <p style="text-align: center; margin: 30px 0;">
                <a href="{{ $feedbackUrl }}" class="button">Provide Employment Reference</a>
            </p>

            <p><strong>Important:</strong> This link will expire after the feedback is submitted or after 30 days, whichever comes first.</p>

            <p>If you have any questions or need assistance, please contact our support team.</p>

            <p>Thank you for your cooperation in maintaining transportation safety standards.</p>

            <p>Best regards,<br>
            Drivelink Team<br>
            support@drivelink.com</p>
        </div>

        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} Drivelink. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
