<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Student;
use App\Models\Professor;
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

        // Création du compte pédagogue (seul utilisateur qui peut se connecter)
        User::create([
            'name' => 'Pédagogue',
            'firstname' => 'Test',
            'email' => 'ped@example.com',
            'password' => bcrypt('password'),
            'role' => 'pedagogue',
        ]);

        // Création de quelques professeurs (qui ne peuvent pas se connecter)
        Professor::create([
            'first_name' => 'Pierre',
            'last_name' => 'Dupont',
            'email' => 'pierre.dupont@example.com',
            'school_email' => 'p.dupont@ifosup.wavre.be',
            'telephone' => '0123456789',
            'adress' => '123 rue des Professeurs',
        ]);

        Professor::create([
            'first_name' => 'Marie',
            'last_name' => 'Martin',
            'email' => 'marie.martin@example.com',
            'school_email' => 'm.martin@ifosup.wavre.be',
            'telephone' => '0123456788',
            'adress' => '456 avenue des Enseignants',
        ]);

        // Création de quelques étudiants (qui ne peuvent pas se connecter)
        Student::create([
            'last_name' => 'Dupont',
            'first_name' => 'Jean',
            'email' => 'jean.dupont@example.com',
            'school_email' => 'j.dupont@ifosup.wavre.be',
            'telephone' => '0123456789',
            'student_id' => 'STU001',
            'current_class' => 'Web Dev 2e année',
            'academic_year' => '2023-2024'
        ]);

        Student::create([
            'last_name' => 'Martin',
            'first_name' => 'Marie',
            'email' => 'marie.martin@example.com',
            'school_email' => 'm.martin@ifosup.wavre.be',
            'telephone' => '0123456788',
            'student_id' => 'STU002',
            'current_class' => 'Web Dev 2e année',
            'academic_year' => '2023-2024'
        ]);

        // Autres seeders pour les modules
        $this->call([
            ModuleSeeder::class,
        ]);
    }
}
