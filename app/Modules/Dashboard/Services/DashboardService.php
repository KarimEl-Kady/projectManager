<?php

namespace App\Modules\Dashboard\Services;

use App\Modules\Project\Enums\ProjectStatus;
use App\Modules\Task\Enums\TaskStatus;
use App\Modules\Task\Models\Task;
use App\Modules\User\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /** @return array<string, int> */
    public function metricsFor(User $user): array
    {
        $projects = $user->projects();
        $projectIds = DB::table('project_users')
            ->select('project_id')
            ->where('user_id', $user->id);
        $tasks = Task::query()->whereIn('project_id', $projectIds);

        return [
            'total_projects' => (clone $projects)->count(),
            'active_projects' => (clone $projects)->where('projects.status', ProjectStatus::Active)->count(),
            'total_tasks' => (clone $tasks)->count(),
            'completed_tasks' => (clone $tasks)->where('status', TaskStatus::Done)->count(),
            'pending_tasks' => (clone $tasks)->where('status', '!=', TaskStatus::Done)->count(),
            'overdue_tasks' => (clone $tasks)
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<', today())
                ->where('status', '!=', TaskStatus::Done)
                ->count(),
        ];
    }
}
