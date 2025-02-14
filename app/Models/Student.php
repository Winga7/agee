<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Student extends Model
{
  protected $fillable = [
    'first_name',
    'last_name',
    'email',
    'school_email',
    'telephone',
    'birth_date',
    'student_id',
    'academic_year',
    'status'
  ];

  public function classes(): BelongsToMany
  {
    return $this->belongsToMany(ClassGroup::class, 'student_class', 'student_id', 'class_id');
  }

  // Relation avec les inscriptions aux cours
  public function courseEnrollments(): HasMany
  {
    return $this->hasMany(CourseEnrollment::class);
  }

  public function evaluationTokens(): HasMany
  {
    return $this->hasMany(EvaluationToken::class, 'student_email', 'email');
  }
}
