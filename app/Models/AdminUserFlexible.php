<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminUserFlexible extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'admin_users';

    // Make all columns fillable to avoid column issues
    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'permissions' => 'array',
    ];

    // Password hashing mutator
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = \Illuminate\Support\Facades\Hash::make($value);
    }

    // Override the name attribute to handle different column names
    public function getNameAttribute()
    {
        // Try different possible column names
        if (isset($this->attributes['name'])) {
            return $this->attributes['name'];
        } elseif (isset($this->attributes['username'])) {
            return $this->attributes['username'];
        } elseif (isset($this->attributes['full_name'])) {
            return $this->attributes['full_name'];
        } elseif (isset($this->attributes['admin_name'])) {
            return $this->attributes['admin_name'];
        } else {
            return $this->email; // fallback to email
        }
    }

    // Set name attribute flexibly
    public function setNameAttribute($value)
    {
        // Try to set the name in whatever column exists
        if (schema()->hasColumn('admin_users', 'name')) {
            $this->attributes['name'] = $value;
        } elseif (schema()->hasColumn('admin_users', 'username')) {
            $this->attributes['username'] = $value;
        } elseif (schema()->hasColumn('admin_users', 'full_name')) {
            $this->attributes['full_name'] = $value;
        } elseif (schema()->hasColumn('admin_users', 'admin_name')) {
            $this->attributes['admin_name'] = $value;
        }
    }

    // Check if user is active
    public function isActive()
    {
        if (isset($this->attributes['status'])) {
            return $this->status === 'Active';
        } elseif (isset($this->attributes['is_active'])) {
            return $this->is_active == 1;
        } elseif (isset($this->attributes['active'])) {
            return $this->active == 1;
        }
        return true; // default to active if no status field
    }

    // Check if user is super admin
    public function isSuperAdmin()
    {
        if (isset($this->attributes['role'])) {
            return $this->role === 'Super Admin';
        } elseif (isset($this->attributes['user_type'])) {
            return $this->user_type === 'super_admin';
        } elseif (isset($this->attributes['level'])) {
            return $this->level === 'super';
        }
        return false;
    }

    // Update last login
    public function updateLastLogin($ip = null)
    {
        $updateData = [];
        
        if (schema()->hasColumn('admin_users', 'last_login_at')) {
            $updateData['last_login_at'] = now();
        }
        
        if (schema()->hasColumn('admin_users', 'last_login_ip') && $ip) {
            $updateData['last_login_ip'] = $ip;
        }
        
        if (!empty($updateData)) {
            $this->update($updateData);
        }
    }

    // Get all possible name columns for this table
    public static function getNameColumns()
    {
        $schema = \Illuminate\Support\Facades\Schema::getColumnListing('admin_users');
        $possibleNameColumns = ['name', 'username', 'full_name', 'admin_name', 'display_name'];
        
        return array_intersect($possibleNameColumns, $schema);
    }

    // Get all available columns
    public static function getTableColumns()
    {
        return \Illuminate\Support\Facades\Schema::getColumnListing('admin_users');
    }
}