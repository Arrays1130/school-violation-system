<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Violation;
use App\Models\StudentCase;
use App\Models\User;

class CaseSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::all();
        $violations = Violation::all();
        $admin = User::first() ?? User::create([
            'name' => 'Dean of Discipline',
            'email' => 'dean@cst.edu.ph',
            'password' => bcrypt('password'),
            'role' => 'Dean',
        ]);

        $scenarios = [
            'V-001' => [
                'Caught wearing civilian attire (t-shirt and shorts) on a regular uniform day at the college lobby.',
                'Student entered the department building wearing slippers and non-prescribed uniform trousers.',
                'Refused to wear the official necktie and school vest during a formal department assembly.',
            ],
            'V-002' => [
                'Attempted to pass through the main gate security without wearing the official student ID card.',
                'Found inside the campus premises without wearing the ID card; claimed it was left in the locker.',
                'Caught using a classmate\'s ID card to tap through the automated security gate.',
            ],
            'V-003' => [
                'Arrived 45 minutes late for the first-period morning class without a valid excuse letter.',
                'Habitual tardiness recorded by the class adviser during the 7:30 AM subject.',
                'Entered the lecture hall 1 hour after the class started, disrupting the ongoing examination.',
            ],
            'V-004' => [
                'Disposed of plastic food wrappers and soft drink bottles under the classroom armchairs.',
                'Caught littering paper cups and snack bags at the open hallway after recess.',
                'Improper disposal of fast-food takeaway boxes on the steps of the main library.',
            ],
            'V-101' => [
                'Caught actively referencing a hidden cheat sheet during the major mid-term examination.',
                'Discovered exchanging answer sheets with a classmate during the finals in the computer laboratory.',
                'Caught using a mobile phone to search for exam answers hidden under the armchair.',
            ],
            'V-102' => [
                'Discovered carving initials and writing vandalism marks on the classroom drafting table.',
                'Caught writing unauthorized drawings and graffiti on the comfort room doors.',
                'Intentionally defaced the department bulletin board by pulling down official notices.',
            ],
            'V-103' => [
                'Involved in a severe verbal harassment incident against a freshman student in the cafeteria.',
                'Reported for posting defamatory remarks and online trolling of a classmate on Facebook groups.',
                'Participated in active group exclusion and cyber-bullying of a fellow student.',
            ],
            'V-104' => [
                'Spotted climbing over the back fence of the campus to cut classes during afternoon sessions.',
                'Left the campus during class hours without a signed gate pass or permission from the adviser.',
                'Reported missing from three consecutive major classes while seen loitering outside school gates.',
            ],
            'V-201' => [
                'Engaged in a physical fight and exchange of blows with another student near the gymnasium.',
                'Initiated a physical confrontation and punched a fellow student outside the department lounge.',
                'Reported for pushing and physically assaulting a classmate inside the science laboratory.',
            ],
            'V-202' => [
                'Caught actively using an electronic vaping device (e-cigarette) inside the college restrooms.',
                'Found in possession of a half-empty alcoholic beverage bottle inside the student locker.',
                'Spotted bringing prohibited vape pods and cartridges inside the classroom premises.',
            ],
            'V-203' => [
                'Reported and proven to have stolen a classmate\'s high-end scientific calculator during exam week.',
                'Attempted to walk out of the university library with reference books that were not checked out.',
                'Caught stealing cash from the bag of a student left in the physical education lockers.',
            ],
            'V-204' => [
                'Submitted a forged medical excuse slip with a falsified signature of a resident physician.',
                'Caught forging the signature of the parent/guardian on the quarterly warning notice.',
                'Falsified the clearance signature of the department head to obtain class enrollment.',
            ],
        ];

        // Create 60 highly realistic cases spread over the last 3 months
        for ($i = 0; $i < 60; $i++) {
            $student = $students->random();
            $violation = $violations->random();
            
            // Pick a realistic scenario description based on the violation code
            $scenarioList = $scenarios[$violation->code] ?? ['Official disciplinary case recorded by the Dean of Discipline.'];
            $scenario = fake()->randomElement($scenarioList);

            $date = fake()->dateTimeBetween('-3 months', 'now');
            
            // Distribute status realistically
            $status = fake()->randomElement(['Pending', 'Pending', 'Closed', 'Hearing Scheduled']);
            
            // Major offenses are highly weighted towards "Hearing Scheduled" or "Closed" (if older)
            if ($violation->severity == 'Major') {
                $status = fake()->randomElement(['Hearing Scheduled', 'Hearing Scheduled', 'Closed']);
            }
            
            // Older cases are naturally resolved/closed
            if ($date < now()->subMonth()) {
                $status = 'Closed';
            }

            StudentCase::create([
                'student_id' => $student->id,
                'violation_id' => $violation->id,
                'description' => $scenario,
                'occurred_at' => $date,
                'status' => $status,
                'created_by' => $admin->id,
            ]);
        }
    }
}
