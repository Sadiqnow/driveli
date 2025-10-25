<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'mission_statement',
        'vision_statement',
        'core_values',
        'social_media_links',
        'facebook_url',
        'twitter_url',
        'linkedin_url',
        'instagram_url',
        'company_history',
        'certifications',
        'awards',
        'employee_count',
        'annual_revenue',
        'headquarters_location',
        'branch_locations',
    ];

    protected $casts = [
        'social_media_links' => 'array',
        'certifications' => 'array',
        'awards' => 'array',
        'branch_locations' => 'array',
        'core_values' => 'array',
        'employee_count' => 'integer',
        'annual_revenue' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
