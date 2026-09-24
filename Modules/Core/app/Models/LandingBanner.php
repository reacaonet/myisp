<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;

class LandingBanner extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'badge',
        'image',
        'link_url',
        'link_label',
        'highlight',
        'title_font_size',
        'title_color',
        'subtitle_font_size',
        'subtitle_color',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'title_font_size' => 'integer',
        'subtitle_font_size' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}