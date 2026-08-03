<?php

namespace App\Modules\Task\Services;

use App\Modules\Core\Services\BaseService;
use App\Modules\Project\Models\Project;
use App\Modules\Task\Models\Task;
use App\Modules\Task\Repositories\TaskRepository;
use App\Modules\Task\Requests\FetchTaskRequest;
use App\Modules\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/** @extends BaseService<TaskRepository> */
class TaskService extends BaseService
{
    public function __construct(TaskRepository $repository)
    {
        parent::__construct($repository);
    }

    /** @return LengthAwarePaginator<Task> */
    public function listForProject(Project $project, FetchTaskRequest $request): LengthAwarePaginator
    {
        return $this->taskRepository()->paginateForProject($project, $request);
    }

    public function findForProjectOrFail(Project $project, string $uuid): Task
    {
        return $this->taskRepository()->findForProjectOrFail($project, $uuid);
    }

    /** @param array<string, mixed> $attributes */
    public function createForProject(Project $project, User $user, array $attributes): Task
    {
        return DB::transaction(function () use ($project, $user, $attributes): Task {
            $attributes['project_id'] = $project->id;

            /** @var Task $task */
            $task = $this->taskRepository()->create($attributes);
            $task->activities()->create([
                'user_id' => $user->id,
                'activity' => 'Task created',
            ]);

            return $task->load('project');
        });
    }

    /** @param array<string, mixed> $attributes */
    public function updateForProject(Project $project, User $user, string $uuid, array $attributes): Task
    {
        return DB::transaction(function () use ($project, $user, $uuid, $attributes): Task {
            $task = $this->findForProjectOrFail($project, $uuid);
            $task->fill($attributes);
            $changed = array_keys($task->getDirty());
            $task->save();

            if ($changed !== []) {
                $task->activities()->create([
                    'user_id' => $user->id,
                    'activity' => 'Task updated: '.implode(', ', $changed),
                ]);
            }

            return $task->refresh()->load('project');
        });
    }

    public function deleteForProject(Project $project, User $user, string $uuid): void
    {
        DB::transaction(function () use ($project, $user, $uuid): void {
            $task = $this->findForProjectOrFail($project, $uuid);
            $task->activities()->create([
                'user_id' => $user->id,
                'activity' => 'Task deleted',
            ]);
            $this->taskRepository()->delete($task);
        });
    }

    private function taskRepository(): TaskRepository
    {
        /** @var TaskRepository */
        return $this->repository;
    }
}
