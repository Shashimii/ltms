<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // default admin user data
        $users = [
            [
                'name' => 'Shashimii',
                'email' => 'shashimii@gmail.com',
                'password' => bcrypt('password'),
                'role' => 2
            ],
            [
                'name' => 'Minji',
                'email' => 'minji@gmail.com',
                'password' => bcrypt('password'),
                'role' => 1
            ],
            [
                'name' => 'Haerin',
                'email' => 'haerin@gmail.com',
                'password' => bcrypt('password'),
                'role' => 0
            ]
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}