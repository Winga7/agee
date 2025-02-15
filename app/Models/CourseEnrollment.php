<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseEnrollment extends Model
{
  protected $fillable = [
    'student_id',
    'module_id',
    'class_id',
    'start_date',
    'end_date'
  ];

  protected $casts = [
    'start_date' => 'date',
    'end_date' => 'date'
  ];

  public function student(): BelongsTo
  {
    return $this->belongsTo(Student::class);
  }

  public function module(): BelongsTo
  {
    return $this->belongsTo(Module::class);
  }

  public function evaluationTokens(): HasMany
  {
    return $this->hasMany(EvaluationToken::class, 'student_email', 'student_email');
  }

  public function class()
  {
    return $this->belongsTo(ClassGroup::class, 'class_id');
  }
}
