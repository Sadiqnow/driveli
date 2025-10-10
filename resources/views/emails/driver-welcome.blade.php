<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to {{ $company_name ?? 'Drivelink' }}!</title>
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
            font-size: 32px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }
        .welcome-banner {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            text-align: center;
            padding: 30px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .welcome-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .steps-container {
            background-color: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            margin: 25px 0;
        }
        .step {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #dee2e6;
        }
        .step:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        .step-number {
            background-color: #007bff;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
            flex-shrink: 0;
        }
        .step-content {
            flex: 1;
        }
        .step-title {
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        .features {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 20px 0;
        }
        .feature {
            display: flex;
            align-items: center;
            padding: 10px;
            background-color: #e7f3ff;
            border-radius: 8px;
        }
        .feature-icon {
            font-size: 20px;
            margin-right: 10px;
        }
        .button {
            display: inline-block;
            padding: 15px 30px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #0056b3;
        }
        .important-note {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
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
        @media (max-width: 600px) {
            .features {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">🚛 {{ $company_name ?? 'Drivelink' }}</div>
        </div>

        <div class="welcome-banner">
            <div class="welcome-title">Welcome to Drivelink!</div>
            <p>Your driver account has been successfully created</p>
        </div>

        <p>Dear {{ $driver->first_name }} {{ $driver->surname }},</p>

        <p>{{ $welcome_message }}</p>

        <p>We're excited to have you join our community of professional drivers. Drivelink connects skilled drivers with companies that need reliable transportation services.</p>

        <div class="features">
            <div class="feature">
                <span class="feature-icon">💼</span>
                <span>Job Opportunities</span>
            </div>
            <div class="feature">
                <span class="feature-icon">💰</span>
                <span>Competitive Rates</span>
            </div>
            <div class="feature">
                <span class="feature-icon">🛡️</span>
                <span>Verified Companies</span>
            </div>
            <div class="feature">
                <span class="feature-icon">📱</span>
                <span>Mobile App Access</span>
            </div>
        </div>

        <div class="steps-container">
            <h3 style="text-align: center; color: #007bff; margin-bottom: 25px;">Getting Started - Next Steps</h3>
            
            @if(isset($next_steps) && is_array($next_steps))
            @foreach($next_steps as $index => $step)
            <div class="step">
                <div class="step-number">{{ $index + 1 }}</div>
                <div class="step-content">
                    <div class="step-title">{{ $step }}</div>
                    @if($index == 0)
                    <small class="text-muted">Add your personal information, contact details, and professional experience.</small>
                    @elseif($index == 1)
                    <small class="text-muted">Upload your NIN, driver's license, passport photo, and any certificates.</small>
                    @elseif($index == 2)
                    <small class="text-muted">Our team will review your information and documents (usually takes 2-5 business days).</small>
                    @elseif($index == 3)
                    <small class="text-muted">Once verified, you'll receive job matches based on your skills and location.</small>
                    @endif
                </div>
            </div>
            @endforeach
            @else
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-content">
                    <div class="step-title">Complete Your Profile</div>
                    <small class="text-muted">Add your personal information, contact details, and professional experience.</small>
                </div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-content">
                    <div class="step-title">Upload Your Documents</div>
                    <small class="text-muted">Upload your NIN, driver's license, passport photo, and any certificates.</small>
                </div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-content">
                    <div class="step-title">Wait for Verification</div>
                    <small class="text-muted">Our team will review your information and documents (usually takes 2-5 business days).</small>
                </div>
            </div>
            <div class="step">
                <div class="step-number">4</div>
                <div class="step-content">
                    <div class="step-title">Start Getting Jobs</div>
                    <small class="text-muted">Once verified, you'll receive job matches based on your skills and location.</small>
                </div>
            </div>
            @endif
        </div>

        <div class="important-note">
            <strong>Important:</strong> Make sure all your documents are clear, valid, and up-to-date. This will help speed up the verification process.
        </div>

        <div style="text-align: center;">
            <a href="{{ route('driver.dashboard') }}" class="button">Complete Your Profile</a>
        </div>

        @if($mobile_app_url && $mobile_app_url !== '#')
        <div style="text-align: center; margin: 20px 0;">
            <p><strong>Download our mobile app:</strong></p>
            <a href="{{ $mobile_app_url }}" style="color: #007bff;">📱 Get the Drivelink App</a>
        </div>
        @endif

        <p>If you have any questions or need assistance getting started, please don't hesitate to reach out to our support team.</p>

        <div class="footer">
            <p><strong>{{ $company_name ?? 'Drivelink' }} Support Team</strong></p>
            <p>
                Email: <a href="mailto:{{ $support_contact }}">{{ $support_contact }}</a><br>
                We're here to help you every step of the way!
            </p>
            <p style="font-size: 12px; color: #999;">
                © {{ date('Y') }} {{ $company_name ?? 'Drivelink' }}. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>