<?php

namespace App\Modules\Project\Tests\Feature;

use App\Modules\Project\Enums\ProjectStatus;
use App\Modules\Project\Models\Project;
use App\Modules\Task\Models\Task;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_view_update_and_delete_a_project(): void
    {
        Sanctum::actingAs($user = User::factory()->create());

        $created = $this->postJson('/api/projects', [
            'title' => 'Assessment API',
            'description' => 'Build the task management API.',
            'status' => 'active',
        ])->assertCreated()->assertJsonPath('data.status', 'active');

        $uuid = $created->json('data.uuid');
        $project = Project::query()->where('uuid', $uuid)->firstOrFail();

        $this->assertTrue($project->users()->whereKey($user->id)->exists());

        $this->getJson("/api/projects/{$uuid}")
            ->assertOk()
            ->assertJsonPath('data.title', 'Assessment API');

        $this->patchJson("/api/projects/{$uuid}", [
            'title' => 'Completed Assessment',
            'status' => 'completed',
        ])->assertOk()
            ->assertJsonPath('data.title', 'Completed Assessment')
            ->assertJsonPath('data.status', 'completed');

        Task::factory()->for($project)->create();

        $this->deleteJson("/api/projects/{$uuid}")->assertNoContent();

        $this->assertSoftDeleted('projects', ['id' => $project->id]);
        $this->assertSoftDeleted('tasks', ['project_id' => $project->id]);
    }

    public function test_project_list_is_paginated_filterable_and_scoped_to_user(): void
    {
        Sanctum::actingAs($user = User::factory()->create());
        $other = User::factory()->create();

        $active = Project::factory()->create(['title' => 'Visible Active', 'status' => ProjectStatus::Active]);
        $completed = Project::factory()->create(['title' => 'Visible Completed', 'status' => ProjectStatus::Completed]);
        $hidden = Project::factory()->create(['title' => 'Hidden Active', 'status' => ProjectStatus::Active]);
        $user->projects()->attach([$active->id, $completed->id]);
        $other->projects()->attach($hidden);

        $this->getJson('/api/projects?status=active&search=Visible&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.uuid', $active->uuid)
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonMissing(['uuid' => $hidden->uuid]);
    }

    public function test_user_cannot_access_another_users_project(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $owner = User::factory()->create();
        $project = Project::factory()->create();
        $owner->projects()->attach($project);

        $this->getJson("/api/projects/{$project->uuid}")
            ->assertNotFound()
            ->assertJsonPath('message', 'Resource not found.');
    }
}
