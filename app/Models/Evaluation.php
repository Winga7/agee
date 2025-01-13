<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'module_id',
        'score',
        'comment',
    ];

    // Relation avec le module
    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    // Optionnel : Relation avec l'utilisateur
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
