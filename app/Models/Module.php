<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'code',
        'professor_id',
        'description'
    ];

    // Relation : un module appartient à un Professeur
    public function professor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'professor_id');
    }

    // Relation : un module a plusieurs évaluations
    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(Classes::class, 'module_classes', 'module_id', 'class_id');
    }

    public function courseEnrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }
}
