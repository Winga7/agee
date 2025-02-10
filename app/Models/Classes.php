<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Classes extends Model
{
  use HasFactory;

  protected $table = 'class_groups';

  protected $fillable = ['name'];

  public function modules(): BelongsToMany
  {
    return $this->belongsToMany(Module::class, 'module_classes', 'class_id', 'module_id');
  }

  public function courseEnrollments(): HasMany
  {
    return $this->hasMany(CourseEnrollment::class, 'class_id');
  }

  public function students(): HasMany
  {
    return $this->hasMany(Student::class, 'class_id');
  }
}
