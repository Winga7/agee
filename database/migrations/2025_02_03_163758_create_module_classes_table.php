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
    Schema::create('module_class_group', function (Blueprint $table) {
      $table->id();
      $table->foreignId('module_id')->constrained()->onDelete('cascade');
      $table->foreignId('class_group_id')->constrained('class_groups')->onDelete('cascade');
      $table->timestamps();

      // Contrainte unique pour éviter les doublons
      $table->unique(['module_id', 'class_group_id']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('module_class_group');
  }
};
