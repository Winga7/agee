<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = [
        'last_name',
        'first_name',
        'email',
        'school_email',
        'telephone',
        'student_id',
        'current_class',
        'academic_year',
        'status'
    ];

    // Relation avec les inscriptions aux cours
    public function courseEnrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function evaluationTokens()
    {
        return $this->hasMany(EvaluationToken::class, 'student_email', 'email');
    }
}
