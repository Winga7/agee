<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Professor extends Model
{
  use HasFactory;

  protected $fillable = [
    'first_name',
    'last_name',
    'email',
    'school_email',
    'telephone',
    'adress',
    'birth_date'
  ];

  // Relation un Professeur a plusieurs Modules
  public function modules()
  {
    return $this->hasMany(Module::class);
  }
}
