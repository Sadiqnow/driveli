<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalLanguage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'native_name',
        'is_major_language',
        'is_active',
    ];

    protected $casts = [
        'is_major_language' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeMajorLanguages($query)
    {
        return $query->where('is_major_language', true);
    }

    public function scopeByCode($query, $code)
    {
        return $query->where('code', $code);
    }

    // Methods
    public function getDisplayNameAttribute()
    {
        return $this->native_name ?: $this->name;
    }

    public static function getMajorLanguages()
    {
        return self::majorLanguages()->active()->orderBy('name')->get();
    }

    public static function getAllLanguages()
    {
        return self::active()->orderBy('is_major_language', 'desc')->orderBy('name')->get();
    }

    public static function getLanguageByCode($code)
    {
        return self::byCode($code)->active()->first();
    }
}