<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    protected $fillable = [
        'form_id',
        'form_section_id',
        'question',
        'type',        // text, textarea, radio, select, rating
        'options',     // JSON pour les options des radio/select
        'order',
        'is_required',
        'controls_visibility'
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean'
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }
}
