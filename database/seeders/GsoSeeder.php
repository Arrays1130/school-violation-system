<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GsoSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'gso@ilinkCST.edu'],
            [
                'name' => 'GSO Officer',
                'password' => Hash::make('password'),
                'role' => 'gso',
                'email_verified_at' => now(),
            ]
        );
    }
}
