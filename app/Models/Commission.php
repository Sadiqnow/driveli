<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Commission extends Model
{
    use HasFactory;

    protected $fillable = [
        'commission_id',
        'driver_id',
        'driver_match_id',
        'company_request_id',
        'amount',
        'rate',
        'base_amount',
        'status',
        'due_date',
        'paid_at',
        'payment_method',
        'payment_reference',
        'disputed_at',
        'dispute_reason',
        'resolved_at',
        'refunded_at',
        'refund_amount',
        'refund_reason',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'rate' => 'decimal:2',
        'base_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'due_date' => 'datetime',
        'paid_at' => 'datetime',
        'disputed_at' => 'datetime',
        'resolved_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    // Relationships
    public function driver()
    {
        return $this->belongsTo(DriverNormalized::class, 'driver_id');
    }

    public function driverMatch()
    {
        return $this->belongsTo(DriverMatch::class);
    }

    public function companyRequest()
    {
        return $this->belongsTo(CompanyRequest::class);
    }

    // Scopes
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }

    public function scopeDisputed($query)
    {
        return $query->where('status', 'disputed');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'unpaid')
                    ->where('due_date', '<', now());
    }

    // Accessors
    public function getStatusColorAttribute()
    {
        $colors = [
            'unpaid' => 'warning',
            'paid' => 'success',
            'disputed' => 'danger',
            'resolved' => 'info',
            'refunded' => 'secondary',
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    public function getIsOverdueAttribute()
    {
        return $this->status === 'unpaid' && $this->due_date < now();
    }
}