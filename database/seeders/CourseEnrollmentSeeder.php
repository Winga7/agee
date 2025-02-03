<?php

namespace Database\Seeders;

use App\Models\CourseEnrollment;
use App\Models\Classes;
use Illuminate\Database\Seeder;

class CourseEnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $webDevClass = Classes::where('name', 'Web Dev 2e année')->first();

        CourseEnrollment::create([
            'student_id' => 1,
            'module_id' => 1,
            'class_id' => $webDevClass->id,
            'start_date' => '2023-09-15',
            'end_date' => '2024-06-30',
        ]);

        CourseEnrollment::create([
            'student_id' => 1,
            'module_id' => 2,
            'class_id' => $webDevClass->id,
            'start_date' => '2023-09-15',
            'end_date' => '2024-06-30',
        ]);

        CourseEnrollment::create([
            'student_id' => 2,
            'module_id' => 1,
            'class_id' => $webDevClass->id,
            'start_date' => '2023-09-15',
            'end_date' => '2024-06-30',
        ]);
    }
}
