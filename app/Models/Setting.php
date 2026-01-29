<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'nama_gereja',
        'alamat',
        'logo_path',
        'maintenance_threshold',
        'currency',
        'auto_download_after_add',
        'auto_download_after_edit',
        'default_pagination',
    ];
}
