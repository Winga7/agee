<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        Module::create([
            'title' => 'Framework Frontend',
            'code' => 'WEB303',
            'professor_id' => 1
        ]);

        Module::create([
            'title' => 'Framework Backend',
            'code' => 'WEB304',
            'professor_id' => 1
        ]);

        Module::create([
            'title' => 'Web Dynamique',
            'code' => 'WEB301',
            'professor_id' => 2
        ]);
    }
}
