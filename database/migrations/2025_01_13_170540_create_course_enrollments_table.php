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
    Schema::create('course_enrollments', function (Blueprint $table) {
      $table->id();
      $table->foreignId('student_id')->constrained()->onDelete('cascade');
      $table->foreignId('module_id')->constrained()->onDelete('cascade');
      $table->foreignId('class_id')->constrained('class_groups')->onDelete('cascade');
      $table->date('start_date');
      $table->date('end_date');
      $table->timestamps();

      // Contrainte unique pour éviter les doublons d'inscription
      $table->unique(['student_id', 'module_id', 'class_id']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('course_enrollments');
  }
};
