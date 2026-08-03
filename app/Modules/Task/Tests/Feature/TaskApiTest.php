<?php

namespace App\Modules\Task\Tests\Feature;

use App\Modules\Project\Models\Project;
use App\Modules\Task\Enums\TaskPriority;
use App\Modules\Task\Enums\TaskStatus;
use App\Modules\Task\Models\Task;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_update_view_and_delete_a_task(): void
    {
        [$user, $project] = $this->authenticatedProject();

        $created = $this->postJson("/api/projects/{$project->uuid}/tasks", [
            'title' => 'Implement API tests',
            'description' => 'Cover the important behavior.',
            'priority' => 'high',
            'status' => 'todo',
            'due_date' => today()->addWeek()->format('Y-m-d'),
        ])->assertCreated()
            ->assertJsonPath('data.priority', 'high')
            ->assertJsonPath('data.status', 'todo');

        $uuid = $created->json('data.uuid');

        $this->getJson("/api/projects/{$project->uuid}/tasks/{$uuid}")
            ->assertOk()
            ->assertJsonPath('data.title', 'Implement API tests');

        $this->patchJson("/api/projects/{$project->uuid}/tasks/{$uuid}", [
            'status' => 'done',
            'priority' => 'medium',
        ])->assertOk()
            ->assertJsonPath('data.status', 'done')
            ->assertJsonPath('data.priority', 'medium');

        $task = Task::query()->where('uuid', $uuid)->firstOrFail();

        $this->assertDatabaseHas('task_activities', [
            'task_id' => $task->id,
            'user_id' => $user->id,
            'activity' => 'Task created',
        ]);
        $this->assertDatabaseHas('task_activities', [
            'task_id' => $task->id,
            'user_id' => $user->id,
            'activity' => 'Task updated: status, priority',
        ]);

        $this->deleteJson("/api/projects/{$project->uuid}/tasks/{$uuid}")->assertNoContent();
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    public function test_task_list_supports_status_priority_and_title_filters(): void
    {
        [, $project] = $this->authenticatedProject();

        $matching = Task::factory()->for($project)->create([
            'title' => 'Urgent API work',
            'status' => TaskStatus::InProgress,
            'priority' => TaskPriority::High,
        ]);
        Task::factory()->for($project)->create([
            'title' => 'Low priority API work',
            'status' => TaskStatus::InProgress,
            'priority' => TaskPriority::Low,
        ]);
        Task::factory()->for($project)->create([
            'title' => 'Urgent completed work',
            'status' => TaskStatus::Done,
            'priority' => TaskPriority::High,
        ]);

        $this->getJson(
            "/api/projects/{$project->uuid}/tasks?status=in_progress&priority=high&search=API",
        )->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.uuid', $matching->uuid);
    }

    public function test_task_must_belong_to_an_accessible_project(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $owner = User::factory()->create();
        $project = Project::factory()->create();
        $owner->projects()->attach($project);
        $task = Task::factory()->for($project)->create();

        $this->getJson("/api/projects/{$project->uuid}/tasks/{$task->uuid}")->assertNotFound();
        $this->patchJson("/api/projects/{$project->uuid}/tasks/{$task->uuid}", [
            'status' => 'done',
        ])->assertNotFound();
    }

    /** @return array{User, Project} */
    private function authenticatedProject(): array
    {
        Sanctum::actingAs($user = User::factory()->create());
        $project = Project::factory()->create();
        $user->projects()->attach($project);

        return [$user, $project];
    }
}
