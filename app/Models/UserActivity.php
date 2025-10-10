<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->created_at = now();
        });
    }

    public function user()
    {
        return $this->belongsTo(AdminUser::class, 'user_id');
    }

    public function model()
    {
        return $this->morphTo();
    }

    public function getActionColorAttribute()
    {
        $colors = [
            'login' => 'success',
            'logout' => 'secondary',
            'create' => 'primary',
            'update' => 'info',
            'delete' => 'danger',
            'restore' => 'success',
            'force_delete' => 'danger',
            'view' => 'light',
            'export' => 'warning',
            'import' => 'info',
            'approve' => 'success',
            'reject' => 'danger',
            'verify' => 'success',
            'suspend' => 'warning',
            'activate' => 'success',
            'deactivate' => 'warning',
        ];

        return $colors[$this->action] ?? 'secondary';
    }

    public function getActionIconAttribute()
    {
        $icons = [
            'login' => 'fas fa-sign-in-alt',
            'logout' => 'fas fa-sign-out-alt',
            'create' => 'fas fa-plus',
            'update' => 'fas fa-edit',
            'delete' => 'fas fa-trash',
            'restore' => 'fas fa-undo',
            'force_delete' => 'fas fa-times',
            'view' => 'fas fa-eye',
            'export' => 'fas fa-download',
            'import' => 'fas fa-upload',
            'approve' => 'fas fa-check',
            'reject' => 'fas fa-times',
            'verify' => 'fas fa-check-circle',
            'suspend' => 'fas fa-pause',
            'activate' => 'fas fa-play',
            'deactivate' => 'fas fa-pause',
        ];

        return $icons[$this->action] ?? 'fas fa-info';
    }

    public static function log($action, $description, $model = null, $oldValues = null, $newValues = null)
    {
        if (!auth('admin')->check()) {
            return;
        }

        $activity = new static([
            'user_id' => auth('admin')->id(),
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        if ($model) {
            $activity->model_type = get_class($model);
            $activity->model_id = $model->id ?? $model->getKey();
        }

        if ($oldValues) {
            $activity->old_values = $oldValues;
        }

        if ($newValues) {
            $activity->new_values = $newValues;
        }

        $activity->save();

        return $activity;
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeWithAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}