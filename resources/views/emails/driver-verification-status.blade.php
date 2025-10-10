<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $company_name }} - Verification Status Update</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }
        .status {
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
            font-weight: bold;
            font-size: 18px;
        }
        .status.verified {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status.rejected {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .status.pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .content {
            margin: 20px 0;
        }
        .notes {
            background-color: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #007bff;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">🚛 {{ $company_name }}</div>
            <h2>Verification Status Update</h2>
        </div>

        <p>Dear {{ $driver->full_name }},</p>

        <div class="content">
            <p>We hope this message finds you well. This is to inform you about an update regarding your driver verification status.</p>
            
            <div class="status {{ $status }}">
                Your verification status: <strong>{{ strtoupper($status) }}</strong>
            </div>

            @if($status === 'verified')
                <p>🎉 <strong>Congratulations!</strong> Your driver verification has been approved. You are now eligible to receive job opportunities through our platform.</p>
                
                <p><strong>What's next?</strong></p>
                <ul>
                    <li>Keep your profile information up to date</li>
                    <li>Check your dashboard regularly for new opportunities</li>
                    <li>Maintain professional standards in all interactions</li>
                </ul>
            @elseif($status === 'rejected')
                <p>We regret to inform you that your driver verification could not be approved at this time.</p>
                
                <p><strong>You can take the following steps:</strong></p>
                <ul>
                    <li>Review the feedback provided below</li>
                    <li>Upload improved documents if requested</li>
                    <li>Contact our support team for assistance</li>
                    <li>Reapply once you've addressed the issues</li>
                </ul>
            @elseif($status === 'pending')
                <p>Your driver verification is currently under review by our team.</p>
                
                <p><strong>Please note:</strong></p>
                <ul>
                    <li>Verification typically takes 2-5 business days</li>
                    <li>Ensure all required documents are uploaded</li>
                    <li>You'll be notified as soon as the review is complete</li>
                </ul>
            @endif

            @if($notes)
            <div class="notes">
                <strong>Additional Notes:</strong><br>
                {{ $notes }}
            </div>
            @endif

            <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
            
            <div style="text-align: center;">
                <a href="{{ $verification_url }}" class="button">View Dashboard</a>
            </div>
        </div>

        <div class="footer">
            <p><strong>{{ $company_name }} Support Team</strong></p>
            <p>
                Email: <a href="mailto:{{ $admin_contact }}">{{ $admin_contact }}</a><br>
                This is an automated message. Please do not reply directly to this email.
            </p>
            <p style="font-size: 12px; color: #999;">
                © {{ date('Y') }} {{ $company_name }}. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>