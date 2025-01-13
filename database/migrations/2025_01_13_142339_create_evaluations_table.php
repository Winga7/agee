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
            // Si on stocke l'user_id => anonyme ou non ?
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Lien avec le module
            $table->unsignedBigInteger('module_id');
            $table->foreign('module_id')->references('id')->on('modules')->onDelete('cascade');

            // Lien direct avec le professeur (optionnel, car déjà via le module)
            // $table->unsignedBigInteger('professor_id')->nullable();
            // $table->foreign('professor_id')->references('id')->on('professors')->onDelete('cascade');

            // Champ(s) pour le contenu de l’évaluation : notes, commentaires...
            // Par exemple, "score" (1-5) et "comment" (texte libre).
            $table->tinyInteger('score')->nullable();
            $table->text('comment')->nullable();

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
