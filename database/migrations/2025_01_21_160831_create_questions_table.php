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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->onDelete('cascade');
            $table->foreignId('form_section_id')->constrained('form_sections')->onDelete('cascade');
            $table->string('question');
            $table->enum('type', ['text', 'textarea', 'radio', 'checkbox', 'select', 'rating']);
            $table->json('options')->nullable();
            $table->integer('order');
            $table->boolean('is_required')->default(true);
            $table->boolean('controls_visibility')->default(false);
            $table->timestamps();
        });

        // Ajout de la contrainte de clé étrangère pour depends_on_question_id après la création de la table questions
        Schema::table('form_sections', function (Blueprint $table) {
            $table->foreign('depends_on_question_id')->references('id')->on('questions')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_sections', function (Blueprint $table) {
            $table->dropForeign(['depends_on_question_id']);
        });
        Schema::dropIfExists('questions');
    }
};
