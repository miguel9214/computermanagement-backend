<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'dependency_id',
        'printer_id',
        'scanner_id',
        'device_name',
        'property',
        'status',
        'os',
        'brand',
        'model',
        'cpu',
        'office_package',
        'asset_tag',
        'printer_asset',
        'scanner_asset',
        'ram',
        'hdd',
        'ip',
        'mac',
        'serial',
        'anydesk',
        'operator',
        'notes',
        'created_by_user',
        'updated_by_user'
    ];

    protected $casts = [
        'ram' => 'integer',
        'hdd' => 'integer',
    ];

    public function dependency()
    {
        return $this->belongsTo(Dependency::class);
    }

    public function printer()
    {
        return $this->belongsTo(Printer::class);
    }

    public function scanner()
    {
        return $this->belongsTo(Scanner::class);
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by_user');
    }

    public function updatedByUser()
    {
        return $this->belongsTo(User::class, 'updated_by_user');
    }
}
