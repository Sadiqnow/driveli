<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $company_name ?? 'Drivelink' }} - Document Update</title>
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
        .document-status {
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
            font-weight: bold;
            font-size: 18px;
        }
        .document-status.approved {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .document-status.rejected {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .document-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .document-name {
            font-size: 20px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }
        .action-date {
            color: #666;
            font-size: 14px;
        }
        .notes {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
        }
        .next-steps {
            background-color: #e7f3ff;
            border-left: 4px solid #007bff;
            padding: 15px;
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
            <div class="logo">🚛 {{ $company_name ?? 'Drivelink' }}</div>
            <h2>Document {{ ucfirst($action) }}</h2>
        </div>

        <p>Dear {{ $driver->first_name }} {{ $driver->surname }},</p>

        <div class="document-info">
            <div class="document-name">{{ $document_type_name }}</div>
            <div class="action-date">
                {{ ucfirst($action) }} on {{ $action_date->format('F j, Y \a\t g:i A') }}
            </div>
        </div>

        <div class="document-status {{ $action }}">
            Your {{ $document_type_name }} has been <strong>{{ strtoupper($action) }}</strong>
        </div>

        @if($action === 'approved')
            <div class="next-steps">
                <strong>Great news!</strong> Your {{ $document_type_name }} has been approved by our verification team.
                
                <p><strong>What's next?</strong></p>
                <ul>
                    <li>Keep all your documents current and valid</li>
                    <li>Complete any remaining document submissions</li>
                    <li>Wait for overall verification completion</li>
                    <li>Once fully verified, you'll start receiving job opportunities</li>
                </ul>
            </div>
        @elseif($action === 'rejected')
            <div class="next-steps">
                <strong>Document Review Required:</strong> Your {{ $document_type_name }} requires attention.
                
                <p><strong>Next steps:</strong></p>
                <ul>
                    <li>Review the feedback provided below</li>
                    <li>Ensure your document is clear and legible</li>
                    <li>Upload a new version of the document</li>
                    <li>Contact support if you need assistance</li>
                </ul>
            </div>
        @endif

        @if($notes)
        <div class="notes">
            <strong>Review Notes:</strong><br>
            {{ $notes }}
        </div>
        @endif

        <div style="text-align: center;">
            <a href="{{ route('driver.dashboard') }}" class="button">View Your Dashboard</a>
        </div>

        <p>If you have any questions about this document review, please don't hesitate to contact our support team.</p>

        <div class="footer">
            <p><strong>{{ $company_name ?? 'Drivelink' }} Support Team</strong></p>
            <p>
                Email: <a href="mailto:{{ $admin_contact ?? 'support@drivelink.com' }}">{{ $admin_contact ?? 'support@drivelink.com' }}</a><br>
                This is an automated message. Please do not reply directly to this email.
            </p>
            <p style="font-size: 12px; color: #999;">
                © {{ date('Y') }} {{ $company_name ?? 'Drivelink' }}. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>