<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $company_name ?? 'Drivelink' }} - Document Verification Results</title>
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
        .status-banner {
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
            font-weight: bold;
            font-size: 18px;
        }
        .status-banner.passed {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status-banner.failed {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .status-banner.pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .verification-results {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .document-result {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .document-result:last-child {
            border-bottom: none;
        }
        .document-name {
            font-weight: bold;
        }
        .score {
            padding: 5px 10px;
            border-radius: 15px;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }
        .score.high {
            background-color: #28a745;
        }
        .score.medium {
            background-color: #ffc107;
            color: #212529;
        }
        .score.low {
            background-color: #dc3545;
        }
        .next-steps {
            background-color: #e7f3ff;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 20px 0;
        }
        .important-note {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
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
            <div class="logo">🤖 {{ $company_name ?? 'Drivelink' }}</div>
            <h2>Document Verification Results</h2>
        </div>

        <p>Dear {{ $driver->first_name }} {{ $driver->surname }},</p>

        <p>Your documents have been processed through our automated verification system. Here are the results:</p>

        <div class="status-banner {{ $overall_status }}">
            Overall Verification Status: <strong>{{ strtoupper($overall_status) }}</strong>
        </div>

        <div class="verification-results">
            <h4 style="margin-top: 0; color: #007bff;">Document Verification Scores</h4>
            
            @if($nin_score > 0)
            <div class="document-result">
                <span class="document-name">NIN Document</span>
                <span class="score {{ $nin_score >= 80 ? 'high' : ($nin_score >= 60 ? 'medium' : 'low') }}">
                    {{ $nin_score }}%
                </span>
            </div>
            @endif

            @if($frsc_score > 0)
            <div class="document-result">
                <span class="document-name">Driver's License</span>
                <span class="score {{ $frsc_score >= 80 ? 'high' : ($frsc_score >= 60 ? 'medium' : 'low') }}">
                    {{ $frsc_score }}%
                </span>
            </div>
            @endif
        </div>

        @if($overall_status === 'passed')
            <div class="next-steps">
                <strong>🎉 Congratulations!</strong> Your documents have passed automated verification.
                
                <p><strong>What happens next:</strong></p>
                <ul>
                    @if(isset($next_steps) && is_array($next_steps))
                    @foreach($next_steps as $step)
                    <li>{{ $step }}</li>
                    @endforeach
                    @else
                    <li>Manual review by our verification team</li>
                    <li>You'll receive results within 2-5 business days</li>
                    <li>Once approved, you can start receiving job opportunities</li>
                    @endif
                </ul>
            </div>
        @elseif($overall_status === 'failed')
            <div class="next-steps">
                <strong>⚠️ Action Required:</strong> Some documents need attention.
                
                <p><strong>To improve your verification results:</strong></p>
                <ul>
                    @if(isset($next_steps) && is_array($next_steps))
                    @foreach($next_steps as $step)
                    <li>{{ $step }}</li>
                    @endforeach
                    @else
                    <li>Review and re-upload any failed documents</li>
                    <li>Ensure documents are clear and readable</li>
                    <li>Check that all information is valid and current</li>
                    @endif
                </ul>
            </div>
        @else
            <div class="next-steps">
                <strong>⏳ Processing:</strong> Document verification is in progress.
                
                <p><strong>Please note:</strong></p>
                <ul>
                    @if(isset($next_steps) && is_array($next_steps))
                    @foreach($next_steps as $step)
                    <li>{{ $step }}</li>
                    @endforeach
                    @else
                    <li>Processing usually takes a few minutes</li>
                    <li>You'll receive another email once verification is complete</li>
                    <li>No action is required from you at this time</li>
                    @endif
                </ul>
            </div>
        @endif

        <div class="important-note">
            <strong>Understanding Verification Scores:</strong><br>
            • <strong>80-100%:</strong> Excellent - Document clearly matches your information<br>
            • <strong>60-79%:</strong> Good - Minor discrepancies may need review<br>
            • <strong>Below 60%:</strong> Needs Attention - Please upload a clearer document
        </div>

        <p><strong>Processed on:</strong> {{ $verification_date->format('F j, Y \a\t g:i A') }}</p>

        <div style="text-align: center;">
            <a href="{{ route('driver.dashboard') }}" class="button">View Your Dashboard</a>
        </div>

        <p>Our automated system uses advanced OCR (Optical Character Recognition) technology to verify document authenticity and match information. If you have questions about these results, please contact our support team.</p>

        <div class="footer">
            <p><strong>{{ $company_name ?? 'Drivelink' }} Verification Team</strong></p>
            <p>
                Email: <a href="mailto:{{ $admin_contact ?? 'support@drivelink.com' }}">{{ $admin_contact ?? 'support@drivelink.com' }}</a><br>
                This is an automated verification report.
            </p>
            <p style="font-size: 12px; color: #999;">
                © {{ date('Y') }} {{ $company_name ?? 'Drivelink' }}. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>