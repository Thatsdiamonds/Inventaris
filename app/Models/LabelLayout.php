<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabelLayout extends Model
{
    protected $fillable = [
        'name',
        'width',
        'height',
        'paper_size',
        'margin_top',
        'margin_bottom',
        'margin_left',
        'margin_right',
        'gap_x',
        'gap_y',
        'font_size',
        'visual_elements',
        'custom_css',
        'custom_html',
        'is_active',
    ];

    protected $casts = [
        'width' => 'float',
        'height' => 'float',
        'margin_top' => 'float',
        'margin_bottom' => 'float',
        'margin_left' => 'float',
        'margin_right' => 'float',
        'gap_x' => 'float',
        'gap_y' => 'float',
        'visual_elements' => 'array',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
