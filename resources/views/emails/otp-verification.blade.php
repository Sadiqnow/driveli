@extends('layouts.email')

@section('title', 'Email Verification Code')

@section('content')
<div style="background-color: #f8f9fa; padding: 40px 0;">
    <div style="max-width: 600px; margin: 0 auto; background-color: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
            <h1 style="margin: 0; font-size: 28px; font-weight: 600;">
                <span style="font-size: 32px;">🔐</span><br>
                Email Verification
            </h1>
            <p style="margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;">
                DriveLink - Driver Registration
            </p>
        </div>

        <!-- Content -->
        <div style="padding: 40px 30px;">
            <h2 style="color: #333; font-size: 24px; margin-bottom: 20px;">
                Hello {{ $driver_name }},
            </h2>
            
            <p style="color: #666; font-size: 16px; line-height: 1.6; margin-bottom: 25px;">
                To complete your driver registration with DriveLink, please verify your email address using the verification code below.
            </p>

            <!-- OTP Code -->
            <div style="background: #f8f9fa; border: 2px dashed #007bff; border-radius: 10px; padding: 30px; text-align: center; margin: 30px 0;">
                <p style="color: #666; font-size: 14px; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 1px;">
                    Your Verification Code
                </p>
                <div style="font-size: 48px; font-weight: bold; color: #007bff; letter-spacing: 8px; font-family: monospace; margin: 10px 0;">
                    {{ $otp }}
                </div>
                <p style="color: #999; font-size: 12px; margin-top: 10px;">
                    Enter this code in the verification form
                </p>
            </div>

            <!-- Instructions -->
            <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 20px; margin: 25px 0;">
                <h4 style="color: #856404; margin: 0 0 10px 0; font-size: 16px;">
                    📋 Instructions:
                </h4>
                <ul style="color: #856404; font-size: 14px; margin: 0; padding-left: 20px;">
                    <li>This code will expire in <strong>{{ $expires_in }} minutes</strong></li>
                    <li>Enter the code exactly as shown above</li>
                    <li>Do not share this code with anyone</li>
                    <li>If you didn't request this, please ignore this email</li>
                </ul>
            </div>

            <!-- Action Button -->
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ config('app.url') }}" 
                   style="display: inline-block; background: #007bff; color: white; text-decoration: none; padding: 15px 30px; border-radius: 8px; font-weight: 600; font-size: 16px;">
                    Complete Registration
                </a>
            </div>

            <p style="color: #999; font-size: 14px; line-height: 1.6; margin-top: 30px;">
                If you're having trouble with the verification process, please contact our support team.
            </p>
        </div>

        <!-- Footer -->
        <div style="background: #f8f9fa; padding: 20px 30px; text-align: center; border-radius: 0 0 10px 10px;">
            <p style="color: #666; font-size: 14px; margin: 0;">
                <strong>DriveLink</strong><br>
                Professional Driver Management Platform
            </p>
            <p style="color: #999; font-size: 12px; margin: 10px 0 0 0;">
                This email was sent to {{ $driver_name }} for account verification purposes.
            </p>
        </div>
    </div>

    <!-- Security Notice -->
    <div style="max-width: 600px; margin: 20px auto; text-align: center;">
        <p style="color: #999; font-size: 12px; line-height: 1.5;">
            🔒 <strong>Security Notice:</strong> DriveLink will never ask you to share your verification code via phone or email. 
            This code is only for use on our official platform.
        </p>
    </div>
</div>
@endsection

@section('styles')
<style>
    .otp-digits {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin: 20px 0;
    }
    
    .otp-digit {
        width: 50px;
        height: 50px;
        border: 2px solid #007bff;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        font-weight: bold;
        color: #007bff;
        background: white;
    }
    
    @media only screen and (max-width: 600px) {
        .otp-digits {
            gap: 5px;
        }
        
        .otp-digit {
            width: 40px;
            height: 40px;
            font-size: 20px;
        }
    }
</style>
@endsection