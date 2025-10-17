<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverManagementComparisonReport extends Model
{
    use HasFactory;

    protected $table = 'driver_management_comparison_report';

    protected $fillable = [
        'report_type',
        'old_admin_features',
        'old_superadmin_features',
        'new_features',
        'resolved_issues',
        'unchanged_components',
        'rbac_implementation',
        'performance_metrics',
        'security_enhancements',
        'api_endpoints',
        'database_changes',
        'ui_ux_improvements',
        'testing_results',
        'generated_by',
        'generated_at',
        'summary',
        'recommendations',
    ];

    protected $casts = [
        'old_admin_features' => 'array',
        'old_superadmin_features' => 'array',
        'new_features' => 'array',
        'resolved_issues' => 'array',
        'unchanged_components' => 'array',
        'rbac_implementation' => 'array',
        'performance_metrics' => 'array',
        'security_enhancements' => 'array',
        'api_endpoints' => 'array',
        'database_changes' => 'array',
        'ui_ux_improvements' => 'array',
        'testing_results' => 'array',
        'generated_at' => 'datetime',
    ];
}
