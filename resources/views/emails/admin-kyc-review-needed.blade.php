<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KYC Review Required - DriveLink Admin</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #dc3545, #fd7e14); color: white; padding: 30px 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: white; padding: 30px 20px; border: 1px solid #e0e0e0; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; border-radius: 0 0 8px 8px; }
        .button { display: inline-block; background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .button.primary { background: #dc3545; }
        .button.secondary { background: #28a745; }
        .info-card { background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 5px; padding: 20px; margin: 20px 0; }
        .urgent { background: #ffe6e6; border: 1px solid #ff9999; border-radius: 5px; padding: 15px; margin: 20px 0; }
        .driver-info { background: #e7f3ff; border: 1px solid #007bff; border-radius: 5px; padding: 15px; margin: 20px 0; }
        .stats { display: flex; justify-content: space-between; margin: 20px 0; }
        .stat-item { text-align: center; flex: 1; }
        .stat-number { font-size: 24px; font-weight: bold; color: #007bff; }
        .document-list { margin: 15px 0; }
        .document-item { background: white; border: 1px solid #ddd; border-radius: 3px; padding: 10px; margin: 5px 0; display: flex; align-items: center; }
        .doc-icon { width: 30px; height: 30px; border-radius: 50%; background: #28a745; color: white; text-align: center; line-height: 30px; margin-right: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🔍 KYC Review Required</h1>
            <p>New driver verification awaiting your review</p>
        </div>
        
        <!-- Main Content -->
        <div class="content">
            <div class="urgent">
                <h3 style="margin: 0 0 10px 0; color: #dc3545;">⚡ Action Required</h3>
                <p style="margin: 0;">A new driver has completed their KYC verification and requires admin review within the next 24-48 hours.</p>
            </div>
            
            <div class="driver-info">
                <h3 style="margin: 0 0 15px 0; color: #007bff;">👤 Driver Information</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div><strong>Name:</strong> {{ $driver->full_name }}</div>
                    <div><strong>Driver ID:</strong> {{ $driver->driver_id }}</div>
                    <div><strong>Email:</strong> {{ $driver->email }}</div>
                    <div><strong>Phone:</strong> {{ $driver->phone }}</div>
                    <div><strong>License #:</strong> {{ $driver->driver_license_number }}</div>
                    <div><strong>Submitted:</strong> {{ $completionData['completed_at'] ? \Carbon\Carbon::parse($completionData['completed_at'])->format('M j, Y g:i A') : 'Just now' }}</div>
                </div>
            </div>
            
            <h3>📑 Submitted Documents</h3>
            <div class="document-list">
                @if(!empty($completionData['documents_uploaded']))
                    @foreach($completionData['documents_uploaded'] as $doc)
                        <div class="document-item">
                            <div class="doc-icon">📄</div>
                            <div style="flex: 1;">
                                <strong>{{ ucwords(str_replace('_', ' ', $doc['type'])) }}</strong>
                                @if(isset($doc['file_size']))
                                    <span style="color: #666; font-size: 12px;">
                                        ({{ number_format($doc['file_size'] / 1024 / 1024, 2) }}MB)
                                    </span>
                                @endif
                            </div>
                            <div style="color: #28a745; font-size: 12px;">
                                {{ \Carbon\Carbon::parse($doc['uploaded_at'])->format('M j, g:i A') }}
                            </div>
                        </div>
                    @endforeach
                @else
                    <p style="color: #666; font-style: italic;">No documents found in completion data.</p>
                @endif
            </div>
            
            <h3>✅ Verification Steps Completed</h3>
            <div class="info-card">
                @if(!empty($completionData['steps_completed']))
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; text-align: center;">
                        @foreach($completionData['steps_completed'] as $stepName => $stepData)
                            <div>
                                <div style="font-size: 24px; margin-bottom: 5px;">
                                    {{ $stepData['completed'] ? '✅' : '❌' }}
                                </div>
                                <div style="font-weight: bold; font-size: 12px;">
                                    {{ strtoupper(str_replace('_', ' ', $stepName)) }}
                                </div>
                                @if($stepData['completed'] && isset($stepData['completed_at']))
                                    <div style="font-size: 10px; color: #666;">
                                        {{ \Carbon\Carbon::parse($stepData['completed_at'])->format('M j') }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            
            <h3>🎯 Review Priority</h3>
            <div class="stats">
                <div class="stat-item">
                    <div class="stat-number">{{ count($completionData['documents_uploaded']) }}</div>
                    <div style="font-size: 12px; color: #666;">Documents</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">{{ \Carbon\Carbon::parse($completionData['completed_at'])->diffForHumans() }}</div>
                    <div style="font-size: 12px; color: #666;">Submitted</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" style="color: #dc3545;">HIGH</div>
                    <div style="font-size: 12px; color: #666;">Priority</div>
                </div>
            </div>
            
            <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; padding: 15px; margin: 20px 0;">
                <h4 style="margin: 0 0 10px 0; color: #856404;">⚠️ Review Guidelines</h4>
                <ul style="margin: 0; padding-left: 20px;">
                    <li>Verify all documents are clear and readable</li>
                    <li>Check personal information matches documents</li>
                    <li>Ensure driver license is valid and not expired</li>
                    <li>Review photo quality meets standards</li>
                    <li>Complete review within 24-48 hours</li>
                </ul>
            </div>
            
            <!-- Quick Action Buttons -->
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $reviewUrl }}" class="button primary">🔍 Review Now</a>
                <a href="{{ route('admin.drivers.show', $driver->id) }}" class="button">👤 View Profile</a>
                <a href="{{ route('admin.drivers.index') }}" class="button secondary">📋 All Drivers</a>
            </div>
            
            <hr style="margin: 30px 0;">
            
            <h3>📊 Recent KYC Activity</h3>
            <p style="font-size: 14px; color: #666;">
                This notification was triggered by a completed KYC submission. The driver is now waiting for verification 
                to begin earning with DriveLink. Timely review helps maintain driver satisfaction and platform growth.
            </p>
            
            <div style="background: #e8f4ff; border-left: 4px solid #007bff; padding: 15px; margin: 20px 0;">
                <p style="margin: 0; font-size: 14px;">
                    <strong>💡 Pro Tip:</strong> Use the bulk review tools in the admin dashboard to process multiple KYC applications efficiently during peak hours.
                </p>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p style="margin: 0 0 10px 0;"><strong>DriveLink Admin System</strong></p>
            <p style="margin: 0; font-size: 12px; color: #666;">
                This notification was sent to admin team members<br>
                Driver verification system • {{ date('Y') }} DriveLink<br>
                <a href="{{ route('admin.dashboard') }}" style="color: #007bff;">Admin Dashboard</a> | 
                <a href="{{ route('admin.drivers.index') }}" style="color: #007bff;">Manage Drivers</a>
            </p>
        </div>
    </div>
</body>
</html>