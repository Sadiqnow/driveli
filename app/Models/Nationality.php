<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nationality extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
    ];

    public function drivers()
    {
        return $this->hasMany(Driver::class);
    }
}