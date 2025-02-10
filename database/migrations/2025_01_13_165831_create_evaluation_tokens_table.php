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
    Schema::create('evaluation_tokens', function (Blueprint $table) {
      $table->id();
      $table->string('token')->unique();
      $table->foreignId('module_id')->constrained();
      $table->string('student_email');
      $table->foreignId('class_id')->constrained('class_groups')->onDelete('cascade');
      $table->dateTime('expires_at');
      $table->boolean('is_used')->default(false);
      $table->timestamp('used_at')->nullable();
      $table->timestamps();

      // Ajouter directement la contrainte unique sur student_email et module_id
      $table->unique(['student_email', 'module_id'], 'unique_active_token');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('evaluation_tokens');
  }
};
