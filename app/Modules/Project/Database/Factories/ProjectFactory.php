<?php

namespace App\Modules\Project\Database\Factories;

use App\Modules\Project\Enums\ProjectStatus;
use App\Modules\Project\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Project> */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'status' => fake()->randomElement(ProjectStatus::cases()),
        ];
    }
}
