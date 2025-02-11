<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EvaluationToken extends Model
{
    protected $fillable = [
        'token',
        'module_id',
        'student_email',
        'class_id',
        'is_used',
        'expires_at',
        'used_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_used' => 'boolean'
    ];

    // Ajoutez cette relation
    public function class()
    {
        return $this->belongsTo(ClassGroup::class, 'class_id');
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public static function generateToken()
    {
        return Str::random(64);
    }

    public function isExpired(): bool
    {
        return Carbon::parse($this->expires_at)->isPast();
    }

    public function isValid(): bool
    {
        return !$this->is_used && !$this->isExpired();
    }

    public function getStatus(): string
    {
        if ($this->is_used) {
            return 'completed';  // Questionnaire rempli
        }
        if ($this->isExpired()) {
            return 'expired';    // Token expiré sans réponse
        }
        return 'pending';       // En attente de réponse
    }
}
