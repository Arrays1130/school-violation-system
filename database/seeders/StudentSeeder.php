<?php

namespace Database\Seeders;

use App\Models\Student;
use Database\Seeders\Concerns\SeedsWithoutFaker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    use SeedsWithoutFaker;

    public function run(): void
    {
        $departments = [
            'Bachelor Of Science In Information System',
            'Bachelor Of Science In Criminology',
            'Bachelor Of Technical Vocational Teachers Education',
            'College Of Business And Accounting Education',
        ];

        $sections = ['A', 'B', 'C', 'D'];
        $yearLevels = ['1', '2', '3', '4'];

        $filipinoFirstNames = [
            'Juan', 'Maria', 'Jose', 'Angelo', 'Jayson', 'Ramil', 'Manuel', 'Ricardo', 'Antonio', 'Crisanto',
            'Janice', 'Rhea', 'Camille', 'Shaira', 'Princess', 'Precious', 'Abigail', 'Rachelle', 'Jovelyn', 'Angelica',
            'Christian', 'John Paul', 'Joshua', 'Aldrin', 'Jerome', 'Mark', 'Arnel', 'Jeffrey', 'Dexter', 'Renato',
            'Michelle', 'Roxanne', 'Jonalyn', 'Mary Joy', 'Jocelyn', 'Sheryl', 'Gemma', 'Marites', 'Kyla', 'Andrea',
        ];

        $filipinoLastNames = [
            'Dela Cruz', 'Santos', 'Reyes', 'Aquino', 'Santiago', 'Mendoza', 'Bautista', 'Garcia', 'Cruz', 'Diaz',
            'Gonzales', 'Villanueva', 'Ramos', 'Castro', 'Mercado', 'Flores', 'Del Rosario', 'Pascual', 'Valenzuela', 'Soriano',
            'Alcantara', 'Aquino', 'Mangahas', 'San Jose', 'Tolentino', 'Corpuz', 'Dizon', 'Salazar', 'Bermudez', 'Beltran',
        ];

        $guardians = [
            ['name' => 'Roberto', 'relation' => 'Father'],
            ['name' => 'Helen', 'relation' => 'Mother'],
            ['name' => 'Alicia', 'relation' => 'Mother'],
            ['name' => 'Eduardo', 'relation' => 'Father'],
            ['name' => 'Teresa', 'relation' => 'Mother'],
            ['name' => 'Fernando', 'relation' => 'Father'],
            ['name' => 'Lourdes', 'relation' => 'Mother'],
            ['name' => 'Gregorio', 'relation' => 'Father'],
        ];

        for ($i = 0; $i < 50; $i++) {
            $firstName = $this->pick($filipinoFirstNames);
            $lastName = $this->pick($filipinoLastNames);
            $fullName = $firstName.' '.$lastName;

            $dept = $this->pick($departments);
            $year = $this->pick($yearLevels);
            $sec = $this->pick($sections);
            $sectionString = $year.'-'.$sec;

            $emailName = strtolower(str_replace(' ', '', $firstName.'.'.$lastName));
            $email = $emailName.$i.'@cst.edu.ph';

            $g = $this->pick($guardians);
            $guardianName = $g['name'].' '.$lastName;
            $guardianEmail = strtolower($g['name'].'.'.$lastName).'@gmail.com';

            Student::create([
                'full_name' => $fullName,
                'section' => $sectionString,
                'year_level' => $year,
                'academic_year' => 'SY 2024-2025',
                'department' => $dept,
                'email' => $email,
                'guardian_name' => $guardianName,
                'guardian_email' => $guardianEmail,
                'guardian_phone' => $this->randomPhone(),
                'password' => Hash::make('password'),
            ]);
        }
    }
}
