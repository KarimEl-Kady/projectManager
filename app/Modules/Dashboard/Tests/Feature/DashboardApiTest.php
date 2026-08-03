<?php

namespace App\Modules\Dashboard\Tests\Feature;

use App\Modules\Project\Enums\ProjectStatus;
use App\Modules\Project\Models\Project;
use App\Modules\Task\Enums\TaskStatus;
use App\Modules\Task\Models\Task;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_returns_metrics_for_only_the_authenticated_users_projects(): void
    {
        Sanctum::actingAs($user = User::factory()->create());
        $other = User::factory()->create();

        $active = Project::factory()->create(['status' => ProjectStatus::Active]);
        $completed = Project::factory()->create(['status' => ProjectStatus::Completed]);
        $hidden = Project::factory()->create(['status' => ProjectStatus::Active]);
        $user->projects()->attach([$active->id, $completed->id]);
        $other->projects()->attach($hidden);

        Task::factory()->for($active)->create([
            'status' => TaskStatus::Todo,
            'due_date' => today()->subDay(),
        ]);
        Task::factory()->for($active)->create([
            'status' => TaskStatus::InProgress,
            'due_date' => today()->addDay(),
        ]);
        Task::factory()->for($completed)->create([
            'status' => TaskStatus::Done,
            'due_date' => today()->subWeek(),
        ]);
        Task::factory()->for($hidden)->create([
            'status' => TaskStatus::Todo,
            'due_date' => today()->subWeek(),
        ]);

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertExactJson([
                'data' => [
                    'total_projects' => 2,
                    'active_projects' => 1,
                    'total_tasks' => 3,
                    'completed_tasks' => 1,
                    'pending_tasks' => 2,
                    'overdue_tasks' => 1,
                ],
            ]);
    }
}
