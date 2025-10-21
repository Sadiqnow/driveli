<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Route extends Model
{
    use HasFactory;

    protected $table = 'routes';

    protected $fillable = [
        'name',
        'uri',
        'methods',
        'controller',
        'action',
        'middleware',
        'is_active'
    ];

    protected $casts = [
        'methods' => 'array',
        'middleware' => 'array',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the route permissions for this route
     */
    public function routePermissions(): HasMany
    {
        return $this->hasMany(RoutePermission::class, 'route_name', 'name');
    }

    /**
     * Scope for active routes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for filtering by URI pattern
     */
    public function scopeByUri($query, string $uri)
    {
        return $query->where('uri', 'like', "%{$uri}%");
    }

    /**
     * Scope for filtering by controller
     */
    public function scopeByController($query, string $controller)
    {
        return $query->where('controller', $controller);
    }
}
