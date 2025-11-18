<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'maintenance_id',
        'image_path',
        'image_type',
        'description',
        'order',
        'created_by_user',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    protected $appends = ['image_url'];

    // Relación con el mantenimiento
    public function maintenance()
    {
        return $this->belongsTo(Maintenance::class);
    }

    // Accessor para URL de la imagen
    public function getImageUrlAttribute()
    {
        if ($this->image_path) {
            return url('storage/' . $this->image_path);
        }
        return null;
    }

    // Relación con el usuario creador
    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by_user');
    }
}
