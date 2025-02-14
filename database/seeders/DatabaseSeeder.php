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
  public function run(): void
  {
    $faker = Faker::create('be_BE');

    // Création du compte pédagogue
    User::create([
      'name' => 'Pédagogue',
      'firstname' => 'Test',
      'email' => 'ped@example.com',
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
          '/[^a-zA-Z]/',
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
          '/[^a-zA-Z]/',
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
        'telephone' => $faker->phoneNumber,
        'adress' => $faker->address,
        'birth_date' => $faker->dateTimeBetween('-60 years', '-25 years')->format('Y-m-d'),
      ]);
    }

    // Création de 20 classes
    $classes = [];
    $specialities = ['Web Dev', 'Design', 'Marketing', 'Comptabilité', 'Informatique'];
    $years = ['1ère', '2ème', '3ème'];

    // Modification de la création des classes
    $usedCombinations = [];
    for ($i = 0; $i < 20; $i++) {
      do {
        $speciality = $faker->randomElement($specialities);
        $year = $faker->randomElement($years);
        $className = "$speciality $year année";
      } while (in_array($className, $usedCombinations) && count($usedCombinations) < count($specialities) * count($years));

      // Si toutes les combinaisons possibles sont utilisées, arrêtez la création
      if (count($usedCombinations) >= count($specialities) * count($years)) {
        break;
      }

      $usedCombinations[] = $className;
      $classes[] = DB::table('class_groups')->insertGetId([
        'name' => $className,
        'created_at' => now(),
        'updated_at' => now()
      ]);
    }

    // Création de modules avec des noms cohérents
    $modulesList = [
      // Web Dev
      ['title' => 'HTML/CSS Fondamentaux', 'code' => 'WEB101'],
      ['title' => 'JavaScript Avancé', 'code' => 'WEB202'],
      ['title' => 'PHP & MySQL', 'code' => 'WEB303'],
      ['title' => 'Framework Laravel', 'code' => 'WEB404'],

      // Design
      ['title' => 'Design UI/UX', 'code' => 'DES101'],
      ['title' => 'Adobe Photoshop', 'code' => 'DES202'],
      ['title' => 'Adobe Illustrator', 'code' => 'DES303'],
      ['title' => 'Motion Design', 'code' => 'DES404'],

      // Marketing
      ['title' => 'Marketing Digital', 'code' => 'MKT101'],
      ['title' => 'Réseaux Sociaux', 'code' => 'MKT202'],
      ['title' => 'SEO/SEA', 'code' => 'MKT303'],
      ['title' => 'Analyse de Données', 'code' => 'MKT404'],

      // Comptabilité
      ['title' => 'Comptabilité Générale', 'code' => 'CPT101'],
      ['title' => 'Fiscalité', 'code' => 'CPT202'],
      ['title' => 'Gestion Financière', 'code' => 'CPT303'],
      ['title' => 'Audit Comptable', 'code' => 'CPT404'],

      // Informatique
      ['title' => 'Systèmes d\'exploitation', 'code' => 'INF101'],
      ['title' => 'Réseaux', 'code' => 'INF202'],
      ['title' => 'Sécurité Informatique', 'code' => 'INF303'],
      ['title' => 'Base de données', 'code' => 'INF404']
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

    // Création de 20 étudiants
    $students = [];
    for ($i = 0; $i < 20; $i++) {
      $firstName = $faker->firstName;
      $lastName = $faker->lastName;

      // Création de l'email principal (première lettre du prénom + nom)
      $mainEmail = strtolower(
        preg_replace(
          '/[^a-zA-Z]/',
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
          '/[^a-zA-Z]/',
          '',
          iconv(
            'UTF-8',
            'ASCII//TRANSLIT//IGNORE',
            $firstName . '.' . $lastName
          )
        )
      ) . '@ifosup.wavre.be';

      $students[] = Student::create([
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $mainEmail,
        'school_email' => $schoolEmail,
        'telephone' => $faker->phoneNumber,
        'birth_date' => $faker->dateTimeBetween('-30 years', '-18 years')->format('Y-m-d'),
        'student_id' => 'STU' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
        'academic_year' => '2023-2024',
        'class_id' => $faker->randomElement($classes)
      ]);
    }

    // Création des inscriptions aux cours (plusieurs cours par étudiant)
    foreach ($students as $student) {
      $moduleCount = rand(2, 5);
      $studentModules = $faker->randomElements($modules, $moduleCount);

      foreach ($studentModules as $module) {
        CourseEnrollment::create([
          'student_id' => $student->id,
          'module_id' => $module->id,
          'class_id' => $student->class_id,
          'start_date' => '2023-09-01',
          'end_date' => '2024-06-30',
        ]);
      }
    }
  }
}
