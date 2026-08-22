<?php

namespace Database\Seeders;

use App\Modules\Project\Models\Project;
use App\Modules\Task\Models\Task;
use App\Modules\User\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = User::factory(4)->create();
        $users->prepend(User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'phone' => '+201000000000',
            'password' => 'password',
        ]));

        $users->each(function (User $user): void {
            Project::factory(3)->create()->each(function (Project $project) use ($user): void {
                $project->users()->attach($user);

                Task::factory(5)
                    ->for($project)
                    ->create()
                    ->each(function (Task $task) use ($user): void {
                        $task->notes()->create([
                            'description' => \Faker\Factory::create()->sentence(),
                        ]);
                        $task->activities()->create([
                            'user_id' => $user->id,
                            'activity' => 'Task seeded',
                        ]);
                    });
            });
        });
    }
}
