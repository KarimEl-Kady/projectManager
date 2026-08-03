<?php

namespace App\Modules\Task\Repositories;

use App\Modules\Core\Repositories\BaseRepository;
use App\Modules\Project\Models\Project;
use App\Modules\Task\Models\Task;
use App\Modules\Task\Requests\FetchTaskRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/** @extends BaseRepository<Task> */
class TaskRepository extends BaseRepository
{
    public function __construct(Task $model)
    {
        parent::__construct($model);
    }

    /** @return LengthAwarePaginator<Task> */
    public function paginateForProject(Project $project, FetchTaskRequest $request): LengthAwarePaginator
    {
        $query = $project->tasks()->with('project');

        $query->when(
            $request->filled('status'),
            fn ($query) => $query->where('status', $request->integer('status')),
        );
        $query->when(
            $request->filled('priority'),
            fn ($query) => $query->where('priority', $request->integer('priority')),
        );
        $query->when(
            $request->search(),
            fn ($query, string $search) => $query->where('title', 'like', "%{$search}%"),
        );

        return $query
            ->orderBy($request->sort() ?? 'created_at', $request->direction())
            ->paginate($request->perPage());
    }

    public function findForProjectOrFail(Project $project, string $uuid): Task
    {
        return $project->tasks()->with('project')->where('uuid', $uuid)->firstOrFail();
    }
}
