<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fleet extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'type',
        'total_vehicles',
        'active_vehicles',
        'total_value',
        'manager_name',
        'manager_phone',
        'manager_email',
        'operating_regions',
        'status',
    ];

    protected $casts = [
        'total_vehicles' => 'integer',
        'active_vehicles' => 'integer',
        'total_value' => 'decimal:2',
        'operating_regions' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isOwned(): bool
    {
        return $this->type === 'owned';
    }

    public function isLeased(): bool
    {
        return $this->type === 'leased';
    }

    public function isContracted(): bool
    {
        return $this->type === 'contracted';
    }

    public function updateVehicleCounts(): void
    {
        $this->update([
            'total_vehicles' => $this->vehicles()->count(),
            'active_vehicles' => $this->vehicles()->where('status', 'active')->count(),
        ]);
    }
}
