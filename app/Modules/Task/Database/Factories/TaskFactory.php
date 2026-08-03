<?php

namespace App\Modules\Task\Database\Factories;

use App\Modules\Project\Models\Project;
use App\Modules\Task\Enums\TaskPriority;
use App\Modules\Task\Enums\TaskStatus;
use App\Modules\Task\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Task> */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'project_id' => Project::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'status' => fake()->randomElement(TaskStatus::cases()),
            'priority' => fake()->randomElement(TaskPriority::cases()),
            'due_date' => fake()->optional()->dateTimeBetween('-1 week', '+1 month')?->format('Y-m-d'),
        ];
    }
}
