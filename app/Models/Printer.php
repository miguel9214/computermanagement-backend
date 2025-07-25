<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Printer extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'brand',
        'model',
        'connection',
        'ip',
        'mac',
        'created_by_user',
        'updated_by_user',
    ];

    public function devices() {
        return $this->hasMany(Device::class);
    }
}
