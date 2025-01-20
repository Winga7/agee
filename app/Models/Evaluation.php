<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_hash',
        'module_id',
        'score',
        'original_comment',
        'anonymized_comment',
        'is_anonymized',
        'status'
    ];

    protected $casts = [
        'is_anonymized' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($evaluation) {
            if (auth()->check()) {
                // Création d'un hash unique pour l'utilisateur et le module
                $evaluation->user_hash = hash('sha256', auth()->id() . env('APP_KEY'));
            }
        });
    }

    // Relation avec le module
    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    // Ne jamais exposer le commentaire original
    protected $hidden = ['original_comment', 'user_hash'];

    public function markAsCompleted()
    {
        $this->status = 'completed';
        $this->save();
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }
}
