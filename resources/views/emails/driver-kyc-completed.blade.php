<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KYC Verification Submitted - DriveLink</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #007bff, #6c5ce7); color: white; padding: 30px 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: white; padding: 30px 20px; border: 1px solid #e0e0e0; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; border-radius: 0 0 8px 8px; }
        .button { display: inline-block; background: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 15px 0; }
        .status-card { background: #e7f5e7; border: 1px solid #28a745; border-radius: 5px; padding: 15px; margin: 20px 0; }
        .timeline { margin: 20px 0; }
        .timeline-item { display: flex; align-items: center; margin: 10px 0; }
        .timeline-icon { width: 20px; height: 20px; border-radius: 50%; background: #28a745; color: white; text-align: center; line-height: 20px; margin-right: 15px; font-size: 12px; }
        .logo { max-width: 150px; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🎉 KYC Verification Submitted!</h1>
            <p>Thank you for completing your identity verification</p>
        </div>
        
        <!-- Main Content -->
        <div class="content">
            <p>Dear {{ $driver->first_name }},</p>
            
            <p>Great news! Your KYC (Know Your Customer) verification has been successfully submitted and is now under review.</p>
            
            <div class="status-card">
                <h3 style="margin: 0 0 10px 0; color: #28a745;">✅ Verification Status: Submitted</h3>
                <p style="margin: 0;"><strong>Submitted on:</strong> {{ $completionData['completed_at'] ? \Carbon\Carbon::parse($completionData['completed_at'])->format('F j, Y \a\t g:i A') : 'Just now' }}</p>
            </div>
            
            <h3>📋 What You've Completed:</h3>
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-icon">✓</div>
                    <div><strong>Personal Information:</strong> Full name, contact details, and address</div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-icon">✓</div>
                    <div><strong>Driver License Details:</strong> License number and validity information</div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-icon">✓</div>
                    <div><strong>Document Upload:</strong> {{ count($completionData['documents_uploaded']) }} documents uploaded and verified</div>
                </div>
            </div>
            
            <h3>⏱️ What Happens Next:</h3>
            <ol>
                <li><strong>Document Review:</strong> Our verification team will review all your submitted documents</li>
                <li><strong>Identity Verification:</strong> We'll verify your identity against official records</li>
                <li><strong>Final Approval:</strong> You'll receive an email notification with the verification result</li>
            </ol>
            
            <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; padding: 15px; margin: 20px 0;">
                <h4 style="margin: 0 0 10px 0; color: #856404;">⏳ Processing Time</h4>
                <p style="margin: 0;">Your verification will typically be completed within <strong>24-48 hours</strong>. We'll send you an email notification as soon as it's ready.</p>
            </div>
            
            <h3>📄 Verification Summary:</h3>
            <ul>
                <li><strong>Driver ID:</strong> {{ $driver->driver_id }}</li>
                <li><strong>Name:</strong> {{ $driver->full_name }}</li>
                <li><strong>Email:</strong> {{ $driver->email }}</li>
                <li><strong>Phone:</strong> {{ $driver->phone }}</li>
                <li><strong>License Number:</strong> {{ $driver->driver_license_number }}</li>
                <li><strong>Documents Submitted:</strong> {{ count($completionData['documents_uploaded']) }} files</li>
            </ul>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ route('driver.dashboard') }}" class="button">View Dashboard</a>
            </div>
            
            <hr style="margin: 30px 0;">
            
            <h3>❓ Need Help?</h3>
            <p>If you have any questions about your verification or need to update any information, please don't hesitate to contact our support team:</p>
            <ul>
                <li>📧 Email: <a href="mailto:support@drivelink.com">support@drivelink.com</a></li>
                <li>📞 Phone: <a href="tel:+234-800-DRIVELINK">+234-800-DRIVELINK</a></li>
                <li>💬 Live Chat: Available on our website</li>
            </ul>
            
            <p><strong>Important:</strong> Please do not reply to this email as it's sent from an automated system. Use the contact methods above for any inquiries.</p>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p style="margin: 0 0 10px 0;"><strong>DriveLink</strong></p>
            <p style="margin: 0; font-size: 12px; color: #666;">
                Your trusted driving partner in Nigeria<br>
                This email was sent to {{ $driver->email }}<br>
                © {{ date('Y') }} DriveLink. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>