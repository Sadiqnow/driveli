<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Job Match Assigned</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="color: #007bff;">New Job Match Assigned!</h1>

        <p>Dear {{ $driver->first_name }} {{ $driver->last_name }},</p>

        <p>Great news! You've been matched with a new job opportunity.</p>

        <div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h3>Match Details:</h3>
            <p><strong>Match ID:</strong> {{ $data['match_id'] }}</p>
            <p><strong>Company:</strong> {{ $data['company_name'] }}</p>
            <p><strong>Commission Rate:</strong> {{ $data['commission_rate'] }}%</p>
        </div>

        <p>Please log into your dashboard to accept or decline this match. You have 24 hours to respond.</p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ url('/driver/dashboard') }}" style="background-color: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">
                View Match Details
            </a>
        </div>

        <p>If you have any questions, please contact our support team.</p>

        <p>Best regards,<br>
        The Drivelink Team</p>
    </div>
</body>
</html>
