<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Form extends Model
{
    protected $fillable = [
        'title',
        'description',
        'banner_image',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(FormSection::class)->orderBy('order');
    }
}
