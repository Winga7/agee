<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Student;
use App\Models\Professor;
use App\Models\CourseEnrollment;
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
            CourseEnrollmentSeeder::class,
        ]);

        // Après la création des étudiants et des modules
        // Création des inscriptions aux cours
        CourseEnrollment::create([
            'student_id' => 1, // ID du premier étudiant (Jean Dupont)
            'module_id' => 1,  // ID du premier module
            'start_date' => '2023-09-01',
            'end_date' => '2024-06-30',
            'class_group' => 'Web Dev 2e année'
        ]);

        CourseEnrollment::create([
            'student_id' => 2, // ID du deuxième étudiant (Marie Martin)
            'module_id' => 1,  // ID du premier module
            'start_date' => '2023-09-01',
            'end_date' => '2024-06-30',
            'class_group' => 'Web Dev 2e année'
        ]);

        // Vous pouvez ajouter d'autres inscriptions pour d'autres modules
        CourseEnrollment::create([
            'student_id' => 1,
            'module_id' => 2,
            'start_date' => '2023-09-01',
            'end_date' => '2024-06-30',
            'class_group' => 'Web Dev 2e année'
        ]);
    }
}
