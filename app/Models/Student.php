<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends Model
{
  protected $fillable = [
    'last_name',
    'first_name',
    'email',
    'school_email',
    'telephone',
    'birth_date',
    'student_id',
    'class_id',
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

  public function class(): BelongsTo
  {
    return $this->belongsTo(ClassGroup::class, 'class_id');
  }
}
