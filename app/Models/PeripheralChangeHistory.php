<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeripheralChangeHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'change_date',
        'change_type',
        'component_name',
        'old_value',
        'new_value',
        'reason',
        'cost',
        'supplier',
        'technician',
        'notes',
        'created_by_user',
        'updated_by_user',
    ];

    protected $casts = [
        'change_date' => 'date',
        'cost' => 'decimal:2',
    ];

    // Relación con el dispositivo
    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    // Relación con el usuario creador
    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by_user');
    }

    // Relación con el usuario que actualizó
    public function updatedByUser()
    {
        return $this->belongsTo(User::class, 'updated_by_user');
    }
}
