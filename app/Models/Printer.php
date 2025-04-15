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
        'connection_type',
        'model'
    ];

    public function devices() {
        return $this->hasMany(Device::class);
    }
}
