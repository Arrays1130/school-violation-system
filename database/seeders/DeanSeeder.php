<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DeanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = ['CBAE', 'CTE', 'CCJE', 'CCE'];

        foreach ($departments as $dept) {
            \App\Models\User::updateOrCreate(
                ['email' => 'dean.' . strtolower($dept) . '@example.com'],
                [
                    'name' => 'Dean of ' . $dept,
                    'password' => \Illuminate\Support\Facades\Hash::make('password'),
                    'role' => 'dean',
                    'department' => $dept,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
