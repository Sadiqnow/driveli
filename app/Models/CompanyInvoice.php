<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'company_id',
        'company_match_id',
        'amount',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'currency',
        'status',
        'due_date',
        'paid_at',
        'payment_method',
        'transaction_reference',
        'description',
        'line_items',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'date',
        'line_items' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function companyMatch(): BelongsTo
    {
        return $this->belongsTo(CompanyMatch::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'overdue' || ($this->due_date && $this->due_date->isPast() && !$this->isPaid());
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function markAsPaid(string $paymentMethod = null, string $transactionRef = null): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_method' => $paymentMethod,
            'transaction_reference' => $transactionRef,
        ]);
    }

    public function markAsOverdue(): void
    {
        $this->update(['status' => 'overdue']);
    }

    public function getOutstandingAmountAttribute(): float
    {
        return $this->isPaid() ? 0 : $this->total_amount;
    }
}
