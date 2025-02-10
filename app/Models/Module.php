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
    'description',
    'code',
    'professor_id'
  ];

  // Relation : un module appartient à un Professeur
  public function professor(): BelongsTo
  {
    return $this->belongsTo(Professor::class, 'professor_id');
  }

  // Relation : un module a plusieurs évaluations
  public function evaluations(): HasMany
  {
    return $this->hasMany(Evaluation::class);
  }

  public function classes()
  {
    return $this->belongsToMany(ClassGroup::class, 'module_class_group', 'module_id', 'class_group_id');
  }

  public function courseEnrollments(): HasMany
  {
    return $this->hasMany(CourseEnrollment::class);
  }
}
