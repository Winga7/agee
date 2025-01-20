<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Création du compte pédagogue
        User::create([
            'name' => 'Pédagogue',
            'email' => 'pedagogue@example.com',
            'password' => bcrypt('password'),
            'role' => 'pedagogue',
        ]);

        // Création des comptes professeurs
        User::create([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => bcrypt('password'),
            'role' => 'professor',
        ]);

        User::create([
            'name' => 'Jane Smith',
            'email' => 'jane.smith@example.com',
            'password' => bcrypt('password'),
            'role' => 'professor',
        ]);

        // Création de quelques comptes étudiants
        User::create([
            'name' => 'Étudiant Test',
            'email' => 'student@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        // Autres seeders...
        $this->call([
            ProfessorSeeder::class,
            ModuleSeeder::class,
        ]);
    }
}
