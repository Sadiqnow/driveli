<?php

return [
    'categories' => [
        'commercial_truck' => 'Commercial Truck Driver',
        'professional' => 'Professional Driver',
        'public' => 'Public Driver',
        'executive' => 'Executive Driver',
    ],

    'employment' => [
        'part_time' => 'Part Time',
        'full_time' => 'Full Time',
        'contract' => 'Contract',
        'assignment' => 'Assignment Based',
    ],

    'status' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'suspended' => 'Suspended',
        'blocked' => 'Blocked',
    ],

    'verification' => [
        'pending' => 'Pending',
        'verified' => 'Verified',
        'rejected' => 'Rejected',
        'reviewing' => 'Under Review',
    ],

    'kyc' => [
        'not_started' => 'Not Started',
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'rejected' => 'Rejected',
        'expired' => 'Expired',
    ],

    'steps' => [
        'step_1' => [
            'title' => 'Basic Profile & Category Selection',
            'description' => 'Set up your basic profile and choose your driver category',
            'progress' => '30% Complete',
        ],
        'step_2' => [
            'title' => 'Category-Specific Requirements',
            'description' => 'Complete requirements specific to your driver category',
            'progress' => '65% Complete',
        ],
        'step_3' => [
            'title' => 'Verification & Onboarding',
            'description' => 'Final verification and platform setup',
            'progress' => '100% Complete',
        ],
    ],

    'registration' => [
        'welcome_title' => 'Join the Global Driver Network',
        'welcome_subtitle' => 'Connect with opportunities worldwide',
        'category_selection_title' => 'What type of driver are you?',
        'category_selection_subtitle' => 'Choose the category that best describes your driving expertise',
        'employment_preference_title' => 'What\'s your employment preference?',
        'employment_preference_subtitle' => 'Select how you prefer to work',
    ],

    'vehicle_types' => [
        'commercial_truck' => [
            'tanker' => 'Tanker Truck',
            'tipper' => 'Tipper Truck',
            'trailer' => 'Trailer Truck',
            'container' => 'Container Truck',
            'flatbed' => 'Flatbed Truck',
            'box_truck' => 'Box Truck',
            'refrigerated' => 'Refrigerated Truck',
        ],
        'professional' => [
            'luxury_sedan' => 'Luxury Sedan',
            'executive_suv' => 'Executive SUV',
            'corporate_van' => 'Corporate Van',
            'limousine' => 'Limousine',
        ],
        'public' => [
            'taxi_sedan' => 'Taxi Sedan',
            'mini_bus' => 'Mini Bus',
            'ride_share' => 'Ride Share Vehicle',
        ],
        'executive' => [
            'luxury_limousine' => 'Luxury Limousine',
            'armored_vehicle' => 'Armored Vehicle',
            'diplomatic_car' => 'Diplomatic Vehicle',
        ],
    ],

    'messages' => [
        'profile_updated' => 'Profile updated successfully!',
        'kyc_step_completed' => 'KYC step completed successfully!',
        'kyc_submitted' => 'KYC submitted for review successfully!',
        'documents_uploaded' => 'Documents uploaded successfully!',
        'verification_pending' => 'Your profile is under review. We\'ll notify you once verified.',
    ],

    'dashboard' => [
        'welcome' => 'Welcome back, :name!',
        'kyc_progress' => 'KYC Progress',
        'profile_completion' => 'Profile Completion',
        'recent_activity' => 'Recent Activity',
        'earnings_overview' => 'Earnings Overview',
        'job_opportunities' => 'Available Opportunities',
    ],
];