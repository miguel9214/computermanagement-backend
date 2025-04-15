<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dependency extends Model
{
    use HasFactory;

   protected $fillable = [
        'name',
        'created_by_user',
        'updated_by_user',
    ];
    protected $casts = [
        'created_by_user' => 'integer',
        'updated_by_user' => 'integer',
    ];

    public function devices() {
        return $this->hasMany(Device::class);
    }
}
