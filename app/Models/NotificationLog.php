<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'recipient',
        'template_id',
        'template_name',
        'variables',
        'status',
        'sent_at',
        'delivered_at',
        'error_message',
        'created_by'
    ];

    protected $casts = [
        'variables' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime'
    ];

    /**
     * Get the admin user who created this log
     */
    public function creator()
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }

    /**
     * Get the email template (polymorphic relationship)
     */
    public function emailTemplate()
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }

    /**
     * Get the SMS template (polymorphic relationship)
     */
    public function smsTemplate()
    {
        return $this->belongsTo(SmsTemplate::class, 'template_id');
    }

    /**
     * Scope for successful notifications
     */
    public function scopeSuccessful($query)
    {
        return $query->whereIn('status', ['sent', 'delivered']);
    }

    /**
     * Scope for failed notifications
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for email notifications
     */
    public function scopeEmail($query)
    {
        return $query->where('type', 'email');
    }

    /**
     * Scope for SMS notifications
     */
    public function scopeSms($query)
    {
        return $query->where('type', 'sms');
    }

    /**
     * Scope for notifications within date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}
