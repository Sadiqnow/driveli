<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'model',
        'model_id',
        'old_value',
        'new_value',
        'ip_address',
        'description'
    ];

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
    ];

    /**
     * Get the user who performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'user_id');
    }

    /**
     * Log an audit event
     */
    public static function log(string $action, string $model, $modelId = null, array $oldValue = null, array $newValue = null, string $description = null): self
    {
        $userId = auth('admin')->id();
        $ipAddress = request()->ip();

        return static::create([
            'user_id' => $userId,
            'action' => $action,
            'model' => $model,
            'model_id' => $modelId,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'ip_address' => $ipAddress,
            'description' => $description,
        ]);
    }

    /**
     * Scope for filtering by model type
     */
    public function scopeForModel($query, string $model)
    {
        return $query->where('model', $model);
    }

    /**
     * Scope for filtering by action type
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for filtering by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get formatted action description
     */
    public function getFormattedActionAttribute(): string
    {
        return ucfirst($this->action);
    }

    /**
     * Get formatted timestamp
     */
    public function getFormattedTimestampAttribute(): string
    {
        return $this->created_at->format('M d, Y H:i:s');
    }
}
