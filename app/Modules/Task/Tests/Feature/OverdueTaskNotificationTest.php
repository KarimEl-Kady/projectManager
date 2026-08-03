<?php

namespace App\Modules\Task\Tests\Feature;

use App\Modules\Project\Models\Project;
use App\Modules\Task\Enums\TaskStatus;
use App\Modules\Task\Jobs\NotifyOverdueTasks;
use App\Modules\Task\Models\Task;
use App\Modules\Task\Notifications\TaskOverdueNotification;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OverdueTaskNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_overdue_tasks_notify_project_users_only_once(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $project->users()->attach($user);
        $task = Task::factory()->for($project)->create([
            'status' => TaskStatus::Todo,
            'due_date' => today()->subDay(),
        ]);

        (new NotifyOverdueTasks)->handle();
        (new NotifyOverdueTasks)->handle();

        Notification::assertSentToTimes($user, TaskOverdueNotification::class, 1);
        $this->assertNotNull($task->refresh()->overdue_notified_at);
    }
}
