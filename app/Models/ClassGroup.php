<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClassGroup extends Model
{
  use HasFactory;

  protected $table = 'class_groups';

  protected $fillable = [
    'name'
  ];

  public function students(): BelongsToMany
  {
    return $this->belongsToMany(Student::class, 'student_class', 'class_id', 'student_id');
  }

  public function courseEnrollments(): HasMany
  {
    return $this->hasMany(CourseEnrollment::class, 'class_id');
  }

  public function modules(): BelongsToMany
  {
    return $this->belongsToMany(Module::class, 'module_class_group', 'class_group_id', 'module_id');
  }
}
