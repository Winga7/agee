<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EvaluationToken extends Model
{
    protected $fillable = [
        'token',
        'module_id',
        'is_used',
        'expires_at'
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'expires_at' => 'datetime'
    ];

    public static function generateToken(): string
    {
        return Str::random(64);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function isValid(): bool
    {
        return !$this->is_used && $this->expires_at->isFuture();
    }
}
