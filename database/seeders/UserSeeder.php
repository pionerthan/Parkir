<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Scara',
            'email' => 'scara4ein@gmail.com',
            'password' => Hash::make('ein4scaralop'),
            'role' => 'owner',
        ]);

        User::create([
            'name' => 'Kae',
            'email' => 'kaedehara@gmail.com',
            'password' => Hash::make('ilopkazuscara'),
            'role' => 'petugas',
        ]);
    }
}
