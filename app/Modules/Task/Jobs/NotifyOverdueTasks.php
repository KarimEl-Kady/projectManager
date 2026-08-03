<?php

namespace App\Modules\Task\Jobs;

use App\Modules\Task\Enums\TaskStatus;
use App\Modules\Task\Models\Task;
use App\Modules\Task\Notifications\TaskOverdueNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class NotifyOverdueTasks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Task::query()
            ->with('project.users')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', today())
            ->where('status', '!=', TaskStatus::Done)
            ->whereNull('overdue_notified_at')
            ->chunkById(100, function ($tasks): void {
                foreach ($tasks as $task) {
                    Notification::send($task->project->users, new TaskOverdueNotification($task));
                    $task->forceFill(['overdue_notified_at' => now()])->save();
                }
            });
    }
}
