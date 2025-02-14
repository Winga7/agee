<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('student_class', function (Blueprint $table) {
      $table->id();
      $table->foreignId('student_id')->constrained()->onDelete('cascade');
      $table->foreignId('class_id')->constrained('class_groups')->onDelete('cascade');

      // Contrainte unique pour éviter les doublons
      $table->unique(['student_id', 'class_id']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('student_class');
  }
};
