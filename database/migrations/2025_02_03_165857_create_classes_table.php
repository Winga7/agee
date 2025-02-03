<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Copier les classes existantes
        $existingClasses = DB::table('module_classes')
            ->select('class_group')
            ->distinct()
            ->pluck('class_group');

        foreach ($existingClasses as $className) {
            DB::table('classes')->insert([
                'name' => $className,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Ajouter la colonne class_id aux tables existantes
        Schema::table('module_classes', function (Blueprint $table) {
            $table->foreignId('class_id')->nullable()->after('class_group');
        });

        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->foreignId('class_id')->nullable()->after('class_group');
        });

        // Mettre à jour les références
        foreach ($existingClasses as $className) {
            $classId = DB::table('classes')->where('name', $className)->value('id');

            DB::table('module_classes')
                ->where('class_group', $className)
                ->update(['class_id' => $classId]);

            DB::table('course_enrollments')
                ->where('class_group', $className)
                ->update(['class_id' => $classId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('module_classes', function (Blueprint $table) {
            $table->dropColumn('class_id');
        });

        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->dropColumn('class_id');
        });

        Schema::dropIfExists('classes');
    }
};
