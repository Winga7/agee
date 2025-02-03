<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Student;
use App\Models\Module;
use App\Models\CourseEnrollment;
use App\Models\Classes;
use App\Models\Professor;

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

        // Création des classes
        $webDevClass = Classes::create([
            'name' => 'Web Dev 2e année'
        ]);

        $designClass = Classes::create([
            'name' => 'Design 1ère année'
        ]);

        // Création des modules
        $module1 = Module::create([
            'title' => 'Introduction au développement web',
            'code' => 'WEB101',
            'description' => 'Fondamentaux du développement web'
        ]);

        $module2 = Module::create([
            'title' => 'Base de données',
            'code' => 'DB101',
            'description' => 'Introduction aux bases de données'
        ]);

        // Associer les modules aux classes
        $module1->classes()->attach($webDevClass->id);
        $module2->classes()->attach($webDevClass->id);
        $module1->classes()->attach($designClass->id);

        // Création des étudiants
        $student1 = Student::create([
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'jean.dupont@example.com',
            'student_id' => 'STU001',
            'academic_year' => '2023-2024',
            'class_id' => $webDevClass->id
        ]);

        $student2 = Student::create([
            'first_name' => 'Marie',
            'last_name' => 'Martin',
            'email' => 'marie.martin@example.com',
            'student_id' => 'STU002',
            'academic_year' => '2023-2024',
            'class_id' => $webDevClass->id
        ]);

        // Création des inscriptions aux cours
        CourseEnrollment::create([
            'student_id' => $student1->id,
            'module_id' => $module1->id,
            'class_id' => $webDevClass->id,
            'start_date' => '2023-09-01',
            'end_date' => '2024-06-30',
        ]);

        CourseEnrollment::create([
            'student_id' => $student2->id,
            'module_id' => $module1->id,
            'class_id' => $webDevClass->id,
            'start_date' => '2023-09-01',
            'end_date' => '2024-06-30',
        ]);

        CourseEnrollment::create([
            'student_id' => $student1->id,
            'module_id' => $module2->id,
            'class_id' => $webDevClass->id,
            'start_date' => '2023-09-01',
            'end_date' => '2024-06-30',
        ]);
    }
}
