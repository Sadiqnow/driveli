<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setting extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'group',
        'is_public',
        'validation_rules',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'validation_rules' => 'array',
        'value' => 'json'
    ];

    public function creator()
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(AdminUser::class, 'updated_by');
    }

    public function getValueAttribute($value)
    {
        if ($this->type === 'boolean') {
            return json_decode($value);
        }
        if ($this->type === 'array' || $this->type === 'json') {
            return json_decode($value, true);
        }
        if ($this->type === 'integer') {
            return (int) json_decode($value);
        }
        if ($this->type === 'float') {
            return (float) json_decode($value);
        }
        
        return json_decode($value, true);
    }

    public function setValueAttribute($value)
    {
        $this->attributes['value'] = json_encode($value);
    }

    public static function get($key, $default = null, $group = null)
    {
        $query = static::where('key', $key);
        
        if ($group) {
            $query->where('group', $group);
        }
        
        $setting = $query->first();
        
        return $setting ? $setting->value : $default;
    }

    public static function set($key, $value, $type = 'string', $group = 'general', $description = null)
    {
        $setting = static::updateOrCreate(
            ['key' => $key, 'group' => $group],
            [
                'value' => $value,
                'type' => $type,
                'description' => $description,
                'updated_by' => auth('admin')->id()
            ]
        );

        if ($setting->wasRecentlyCreated) {
            $setting->update(['created_by' => auth('admin')->id()]);
        }

        return $setting;
    }

    public static function getGroup($group)
    {
        return static::where('group', $group)
                    ->pluck('value', 'key')
                    ->toArray();
    }

    public static function getAllGroups()
    {
        return static::select('group', 'key', 'value', 'type', 'description', 'is_public')
                    ->get()
                    ->groupBy('group')
                    ->map(function ($settings) {
                        return $settings->mapWithKeys(function ($setting) {
                            return [$setting->key => [
                                'value' => $setting->value,
                                'type' => $setting->type,
                                'description' => $setting->description,
                                'is_public' => $setting->is_public
                            ]];
                        });
                    })
                    ->toArray();
    }
}