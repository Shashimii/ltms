<?php

namespace Database\Seeders;


use App\Models\AssignedTask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class AssignedTaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {   
        // Create 10 duties, 7 officers, and 20 assigned duties
        Task::factory(40)->create();
        User::factory(7)->create();
        AssignedTask::factory(60)->create();
    }
}