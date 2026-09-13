<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
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

    public function down(): void
    {
        User::query()->where('email', 'gso@ilinkCST.edu')->where('role', 'gso')->delete();
    }
};
