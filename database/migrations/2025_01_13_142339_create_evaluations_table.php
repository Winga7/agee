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
    Schema::create('evaluations', function (Blueprint $table) {
      $table->id();

      // Hash anonyme de l'utilisateur
      $table->string('user_hash')->nullable();
      $table->index('user_hash');

      // Relations
      $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
      $table->foreignId('form_id')->constrained('forms')->onDelete('cascade');

      // Données d'évaluation
      $table->json('answers')->nullable();
      $table->enum('status', ['pending', 'completed'])->default('pending');


      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('evaluations');
  }
};
