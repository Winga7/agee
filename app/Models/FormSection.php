<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormSection extends Model
{
    protected $fillable = [
        'form_id',
        'title',
        'description',
        'order',
        'depends_on_question_id',
        'depends_on_answer'
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    public function dependsOnQuestion(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'depends_on_question_id');
    }
}
