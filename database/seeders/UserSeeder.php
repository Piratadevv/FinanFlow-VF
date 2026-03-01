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
            'username' => 'USERtest',
            'name' => 'USERtest',
            'email' => 'usertest@example.com',
            'password' => Hash::make('test123'),
        ]);

        User::create([
            'username' => 'abderrahmane',
            'name' => 'Abderrahmane',
            'email' => 'abderrahmane@example.com',
            'password' => Hash::make('test123'),
        ]);
    }
}
