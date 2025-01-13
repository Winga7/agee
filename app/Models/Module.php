<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'code',
        'professor_id',
    ];

    // Relation : un module appartient à un Professeur
    public function professor()
    {
        return $this->belongsTo(Professor::class);
    }

    // Relation : un module a plusieurs évaluations
    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }
}
