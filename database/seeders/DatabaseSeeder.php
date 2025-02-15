<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Student;
use App\Models\Module;
use App\Models\CourseEnrollment;
use App\Models\ClassGroup;
use App\Models\Professor;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
  // Fonction helper pour générer un numéro de téléphone belge
  private function generateBelgianPhoneNumber($faker)
  {
    $formats = [
      // Mobiles (commençant par 04)
      '04## ## ## ##',
      '+324## ## ## ##',
    ];

    return $faker->numerify($faker->randomElement($formats));
  }

  public function run(): void
  {
    $faker = Faker::create('be_BE');

    // Création du compte pédagogue
    User::create([
      'name' => 'Pédagogue',
      'firstname' => 'Test',
      'email' => 'pedagogue@ifosup.wavre.be',
      'password' => bcrypt('password'),
      'role' => 'pedagogue',
    ]);

    // Création de 20 professeurs
    $professors = [];
    for ($i = 0; $i < 20; $i++) {
      $firstName = $faker->firstName;
      $lastName = $faker->lastName;

      // Création de l'email principal (première lettre du prénom + nom)
      $mainEmail = strtolower(
        preg_replace(
          '/[^a-zA-Z._-]/',
          '',
          iconv(
            'UTF-8',
            'ASCII//TRANSLIT//IGNORE',
            substr($firstName, 0, 1) . $lastName
          )
        )
      ) . '@gmail.com';

      // Création de l'email scolaire (prénom.nom@ifosup.wavre.be)
      $schoolEmail = strtolower(
        preg_replace(
          '/[^a-zA-Z._-]/',
          '',
          iconv(
            'UTF-8',
            'ASCII//TRANSLIT//IGNORE',
            $firstName . '.' . $lastName
          )
        )
      ) . '@ifosup.wavre.be';

      $professors[] = Professor::create([
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $mainEmail,
        'school_email' => $schoolEmail,
        'telephone' => $this->generateBelgianPhoneNumber($faker),
        'adress' => $faker->address,
        'birth_date' => $faker->dateTimeBetween('-60 years', '-25 years')->format('Y-m-d'),
      ]);
    }

    // Création des classes
    $classes = [];
    $specialities = ['BES Webdeveloper', 'BES Webdesigner UI/UX', 'Bachelier en Informatique', 'Bachelier en Comptabilité', 'Bachelier en Marketing'];
    $years = ['1ère', '2ème', '3ème'];

    foreach ($specialities as $speciality) {
      foreach ($years as $year) {
        $className = "$speciality $year année";
        $classes[] = DB::table('class_groups')->insertGetId([
          'name' => $className,
          'created_at' => now(),
          'updated_at' => now()
        ]);
      }
    }

    // Création de modules avec des noms cohérents
    $modulesList = [
      // BES Webdeveloper
      ['title' => 'Scripts serveurs', 'code' => '5XSE2'],
      ['title' => 'Scripts clients', 'code' => '5XSCL'],


      // BES Webdesigner UI/UX
      ['title' => 'Création d\'applications web statiques', 'code' => '5XSTI'],
      ['title' => 'CMS - Niveau 1', 'code' => '5XMS1'],
      ['title' => 'Projet web dynamique', 'code' => '5XDY2'],
      ['title' => 'Langue en situation appliquée à l\'enseignement supérieur UE2', 'code' => '5LAS2'],

      // Bachelier en Informatique
      ['title' => 'Principes d\'algorithmique et de programmation', 'code' => '5IPAP'],
      ['title' => 'Éléments de statistique', 'code' => '5IST4'],
      ['title' => 'Structure des ordinateurs', 'code' => '5ISO4'],
      ['title' => 'Systèmes d\'exploitation', 'code' => '5IOS4'],

      // Bachelier en Comptabilité
      ['title' => 'Taxe sur la valeur ajoutée (TVA)', 'code' => '5CTAX'],
      ['title' => 'Faits et institutions économiques', 'code' => '5CFI'],
      ['title' => 'Informatique: tableur', 'code' => '5CTA'],

      // Bachelier en Marketing
      ['title' => 'Anglais en situation appliqué à l\'enseignement supérieur UE4', 'code' => '5LSA4'],
      ['title' => 'Marketing: séminaire', 'code' => '5MAKS'],
      ['title' => 'Organisation des entreprises et éléments demanagement', 'code' => '5IOE4']


    ];

    $modules = [];
    foreach ($modulesList as $moduleInfo) {
      $modules[] = Module::create([
        'title' => $moduleInfo['title'],
        'code' => $moduleInfo['code'],
        'description' => $faker->paragraph,
        'professor_id' => $faker->randomElement($professors)->id
      ]);
    }

    // Association des modules aux classes (plusieurs classes par module)
    foreach ($modules as $module) {
      $classCount = rand(1, 3);
      $moduleClasses = $faker->randomElements($classes, $classCount);
      $module->classes()->attach($moduleClasses);
    }

    // Création des étudiants avec classes multiples
    for ($i = 0; $i < 20; $i++) {
      $firstName = $faker->firstName;
      $lastName = $faker->lastName;

      $mainEmail = strtolower(
        preg_replace(
          '/[^a-zA-Z._-]/',
          '',
          iconv(
            'UTF-8',
            'ASCII//TRANSLIT//IGNORE',
            substr($firstName, 0, 1) . $lastName
          )
        )
      ) . '@gmail.com';

      $schoolEmail = strtolower(
        preg_replace(
          '/[^a-zA-Z._-]/',
          '',
          iconv(
            'UTF-8',
            'ASCII//TRANSLIT//IGNORE',
            $firstName . '.' . $lastName
          )
        )
      ) . '@ifosup.wavre.be';

      $student = Student::create([
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $mainEmail,
        'school_email' => $schoolEmail,
        'telephone' => $this->generateBelgianPhoneNumber($faker),
        'birth_date' => $faker->dateTimeBetween('-30 years', '-18 years')->format('Y-m-d'),
        'student_id' => 'STU' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
        'academic_year' => '2024-2025',
        'status' => 'active'
      ]);

      // Attribuer 1 à 3 classes aléatoires à chaque étudiant
      $studentClasses = $faker->randomElements($classes, $faker->numberBetween(1, 3));
      $student->classes()->attach($studentClasses);

      // Pour chaque classe de l'étudiant, créer des inscriptions aux modules
      foreach ($studentClasses as $classId) {
        // Sélectionner 2-4 modules aléatoires pour cette classe
        $classModules = $faker->randomElements(
          Module::whereHas('classes', function ($query) use ($classId) {
            $query->where('class_groups.id', $classId);
          })->get()->toArray(),
          min($faker->numberBetween(2, 3), Module::whereHas('classes', function ($query) use ($classId) {
            $query->where('class_groups.id', $classId);
          })->count())
        );

        foreach ($classModules as $module) {
          CourseEnrollment::create([
            'student_id' => $student->id,
            'module_id' => $module['id'],
            'class_id' => $classId,
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
          ]);
        }
      }
    }
  }
}
