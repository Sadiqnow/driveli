<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DriverCompanyRelation extends Model
{
    use HasFactory;

    protected $table = 'driver_company_relations';

    protected $fillable = [
        'driver_id',
        'company_id',
        'status',
        'employment_start_date',
        'employment_end_date',
        'reason_for_leaving',
        'performance_rating',
        'feedback_notes',
        'feedback_token',
        'feedback_requested_at',
        'feedback_submitted_at',
        'last_reminder_sent_at',
        'feedback_requested_by',
        'is_flagged',
        'flag_reason',
    ];

    protected $casts = [
        'employment_start_date' => 'date',
        'employment_end_date' => 'date',
        'feedback_requested_at' => 'datetime',
        'feedback_submitted_at' => 'datetime',
        'last_reminder_sent_at' => 'datetime',
        'is_flagged' => 'boolean',
    ];

    // Relationships
    public function driver()
    {
        return $this->belongsTo(DriverNormalized::class, 'driver_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function feedbackRequestedBy()
    {
        return $this->belongsTo(AdminUser::class, 'feedback_requested_by');
    }

    // Scopes
    public function scopeFlagged($query)
    {
        return $query->where('is_flagged', true);
    }

    public function scopeFeedbackRequested($query)
    {
        return $query->whereNotNull('feedback_requested_at');
    }

    public function scopeFeedbackSubmitted($query)
    {
        return $query->whereNotNull('feedback_submitted_at');
    }

    public function scopePendingFeedback($query)
    {
        return $query->whereNotNull('feedback_requested_at')
                    ->whereNull('feedback_submitted_at');
    }

    public function scopeOverdueFeedback($query, $days = 7)
    {
        return $query->pendingFeedback()
                    ->where('feedback_requested_at', '<', now()->subDays($days));
    }

    // Methods
    public function generateFeedbackToken()
    {
        $this->feedback_token = Str::random(64);
        $this->save();
        return $this->feedback_token;
    }

    public function requestFeedback($adminUser)
    {
        $this->feedback_requested_at = now();
        $this->feedback_requested_by = $adminUser->id;
        $this->generateFeedbackToken();
        $this->save();

        // Log activity
        ActivityLog::create([
            'user_type' => AdminUser::class,
            'user_id' => $adminUser->id,
            'action' => 'feedback_requested',
            'description' => "Feedback requested for driver {$this->driver->full_name} from company {$this->company->name}",
            'metadata' => [
                'driver_id' => $this->driver_id,
                'company_id' => $this->company_id,
                'relation_id' => $this->id,
            ],
        ]);

        return true;
    }

    public function submitFeedback(array $data)
    {
        $this->fill([
            'employment_start_date' => $data['employment_start_date'] ?? $this->employment_start_date,
            'employment_end_date' => $data['employment_end_date'] ?? $this->employment_end_date,
            'performance_rating' => $data['performance_rating'] ?? null,
            'reason_for_leaving' => $data['reason_for_leaving'] ?? null,
            'feedback_notes' => $data['feedback_notes'] ?? null,
            'feedback_submitted_at' => now(),
        ]);

        $this->checkAndFlagDriver();
        $this->save();

        // Log activity
        ActivityLog::create([
            'user_type' => null,
            'user_id' => null,
            'action' => 'feedback_submitted',
            'description' => "Feedback submitted for driver {$this->driver->full_name} from company {$this->company->name}",
            'metadata' => [
                'driver_id' => $this->driver_id,
                'company_id' => $this->company_id,
                'relation_id' => $this->id,
                'performance_rating' => $this->performance_rating,
            ],
        ]);

        return true;
    }

    public function checkAndFlagDriver()
    {
        $shouldFlag = false;
        $flagReasons = [];

        // Flag for very poor performance
        if (in_array($this->performance_rating, ['very_poor', 'poor'])) {
            $shouldFlag = true;
            $flagReasons[] = 'Poor performance rating';
        }

        // Flag for concerning reasons for leaving
        $concerningReasons = [
            'terminated',
            'dismissed',
            'fired',
            'misconduct',
            'accident',
            'safety violation',
            'theft',
            'fraud'
        ];

        if ($this->reason_for_leaving && Str::contains(strtolower($this->reason_for_leaving), $concerningReasons)) {
            $shouldFlag = true;
            $flagReasons[] = 'Concerning reason for leaving';
        }

        // Flag for very short employment (less than 1 month)
        if ($this->employment_start_date && $this->employment_end_date) {
            $durationMonths = $this->employment_start_date->diffInMonths($this->employment_end_date);
            if ($durationMonths < 1) {
                $shouldFlag = true;
                $flagReasons[] = 'Very short employment duration';
            }
        }

        $this->is_flagged = $shouldFlag;
        $this->flag_reason = $shouldFlag ? implode('; ', $flagReasons) : null;

        if ($shouldFlag) {
            // Update driver status if flagged
            $this->driver->update(['status' => 'flagged']);
        }
    }

    public function sendReminder()
    {
        $this->last_reminder_sent_at = now();
        $this->save();

        // Log reminder activity
        ActivityLog::create([
            'user_type' => null,
            'user_id' => null,
            'action' => 'feedback_reminder_sent',
            'description' => "Reminder sent for feedback request to driver {$this->driver->full_name} from company {$this->company->name}",
            'metadata' => [
                'driver_id' => $this->driver_id,
                'company_id' => $this->company_id,
                'relation_id' => $this->id,
            ],
        ]);
    }

    // Accessors
    public function getIsOverdueAttribute()
    {
        return $this->feedback_requested_at && !$this->feedback_submitted_at &&
               $this->feedback_requested_at->addDays(7)->isPast();
    }

    public function getDaysSinceRequestAttribute()
    {
        return $this->feedback_requested_at ? $this->feedback_requested_at->diffInDays(now()) : null;
    }

    public function getDaysSinceSubmissionAttribute()
    {
        return $this->feedback_submitted_at ? $this->feedback_submitted_at->diffInDays(now()) : null;
    }
}
