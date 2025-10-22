<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Driver Assigned to Your Request</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="color: #28a745;">Driver Assigned to Your Request!</h1>

        <p>Dear {{ $company->name }},</p>

        <p>Great news! A driver has been assigned to your request.</p>

        <div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h3>Assignment Details:</h3>
            <p><strong>Match ID:</strong> {{ $data['match_id'] }}</p>
            <p><strong>Driver:</strong> {{ $data['driver_name'] }}</p>
            <p><strong>Commission Rate:</strong> {{ $data['commission_rate'] }}%</p>
        </div>

        <p>The driver will contact you shortly to coordinate the job details. You can track the progress in your dashboard.</p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ url('/company/dashboard') }}" style="background-color: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">
                View Assignment Details
            </a>
        </div>

        <p>If you have any questions, please contact our support team.</p>

        <p>Best regards,<br>
        The Drivelink Team</p>
    </div>
</body>
</html>
