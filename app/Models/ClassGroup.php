<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassGroup extends Model
{
  protected $table = 'class_groups';

  protected $fillable = [
    'name'
  ];

  public function students(): HasMany
  {
    return $this->hasMany(Student::class, 'class_id');
  }

  public function courseEnrollments(): HasMany
  {
    return $this->hasMany(CourseEnrollment::class, 'class_id');
  }
}
