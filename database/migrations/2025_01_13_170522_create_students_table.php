<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('students', function (Blueprint $table) {
      $table->id();
      $table->string('last_name');
      $table->string('first_name');
      $table->string('email')->unique();
      $table->string('school_email')->unique()->nullable();
      $table->string('telephone')->nullable();
      $table->date('birth_date');
      $table->string('student_id')->unique(); // Numéro d'étudiant
      $table->string('academic_year'); // ex: "2023-2024"
      $table->enum('status', ['active', 'inactive', 'graduated'])
        ->default('active');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('students');
  }
};
