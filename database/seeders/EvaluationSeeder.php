<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Evaluation;
use Illuminate\Database\Seeder;

class EvaluationSeeder extends Seeder
{
    public function run()
    {
        $modules = Module::all();
        
        foreach ($modules as $module) {
            // Créer quelques évaluations pour chaque module
            for ($i = 0; $i < rand(5, 10); $i++) {
                Evaluation::create([
                    'module_id' => $module->id,
                    'user_hash' => 'user_' . uniqid(),
                    'score' => rand(1, 5),
                    'status' => rand(0, 1) ? 'completed' : 'pending',
                    'created_at' => now()->subDays(rand(0, 180))
                ]);
            }
        }
    }
}
