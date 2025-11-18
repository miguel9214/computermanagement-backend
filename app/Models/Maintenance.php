<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'maintainable_type',
        'maintainable_id',
        'maintenance_date',
        'next_maintenance_date',
        'maintenance_type',
        'description',
        'performed_tasks',
        'technician',
        'cost',
        'status',
        'notes',
        'physical_format_path',
        'created_by_user',
        'updated_by_user',
    ];

    protected $casts = [
        'maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'cost' => 'decimal:2',
    ];

    protected $appends = ['physical_format_url'];

    // Relación polimórfica
    public function maintainable()
    {
        return $this->morphTo();
    }

    // Accessor para URL del formato físico
    public function getPhysicalFormatUrlAttribute()
    {
        if ($this->physical_format_path) {
            return url('storage/' . $this->physical_format_path);
        }
        return null;
    }

    // Relación con imágenes
    public function images()
    {
        return $this->hasMany(MaintenanceImage::class)->orderBy('order');
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
