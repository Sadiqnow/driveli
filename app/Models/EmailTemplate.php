<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'subject',
        'body',
        'variables',
        'is_active',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean'
    ];

    /**
     * Get the admin user who created this template
     */
    public function creator()
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }

    /**
     * Get the admin user who last updated this template
     */
    public function updater()
    {
        return $this->belongsTo(AdminUser::class, 'updated_by');
    }

    /**
     * Get notification logs for this template
     */
    public function notificationLogs()
    {
        return $this->hasMany(NotificationLog::class, 'template_id');
    }

    /**
     * Scope for active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for templates by name
     */
    public function scopeByName($query, $name)
    {
        return $query->where('name', $name);
    }
}
