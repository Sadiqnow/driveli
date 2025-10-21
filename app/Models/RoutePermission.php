<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoutePermission extends Model
{
    use HasFactory;

    protected $table = 'route_permissions';

    protected $fillable = [
        'route_name',
        'permission_id',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the permission associated with this route permission
     */
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }

    /**
     * Get the route associated with this route permission
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class, 'route_name', 'name');
    }

    /**
     * Scope for active route permissions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for filtering by route name
     */
    public function scopeByRoute($query, string $routeName)
    {
        return $query->where('route_name', $routeName);
    }

    /**
     * Scope for filtering by permission
     */
    public function scopeByPermission($query, $permissionId)
    {
        return $query->where('permission_id', $permissionId);
    }
}
