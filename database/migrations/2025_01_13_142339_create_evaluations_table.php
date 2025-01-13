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

            // Hash anonyme de l'utilisateur (pour éviter les doublons)
            $table->string('user_hash')->nullable();
            $table->index('user_hash');

            // Lien avec le module
            $table->unsignedBigInteger('module_id');
            $table->foreign('module_id')->references('id')->on('modules')->onDelete('cascade');

            // Évaluation
            $table->tinyInteger('score');
            $table->text('original_comment')->nullable();
            $table->text('anonymized_comment')->nullable();
            $table->boolean('is_anonymized')->default(false);

            // Contrainte unique pour empêcher les doubles évaluations
            $table->unique(['user_hash', 'module_id']);

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
