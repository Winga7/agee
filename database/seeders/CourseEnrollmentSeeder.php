<?php

namespace Database\Seeders;

use App\Models\CourseEnrollment;
use Illuminate\Database\Seeder;

class CourseEnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        CourseEnrollment::create([
            'student_id' => 1,
            'module_id' => 1,
            'start_date' => '2023-09-15',
            'end_date' => '2024-06-30',
            'class_group' => 'Web Dev 2e année'
        ]);

        CourseEnrollment::create([
            'student_id' => 1,
            'module_id' => 2,
            'start_date' => '2023-09-15',
            'end_date' => '2024-06-30',
            'class_group' => 'Web Dev 2e année'
        ]);

        CourseEnrollment::create([
            'student_id' => 2,
            'module_id' => 1,
            'start_date' => '2023-09-15',
            'end_date' => '2024-06-30',
            'class_group' => 'Web Dev 2e année'
        ]);
    }
}
