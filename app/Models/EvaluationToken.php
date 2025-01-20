<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EvaluationToken extends Model
{
    protected $fillable = [
        'token',
        'module_id',
        'student_email',
        'is_used',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_used' => 'boolean'
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public static function generateToken(): string
    {
        return Str::random(64);
    }

    public function isValid(): bool
    {
        return !$this->is_used && $this->expires_at->isFuture();
    }
}
