<?php

namespace App\Modules\Project\Repositories;

use App\Modules\Core\Repositories\BaseRepository;
use App\Modules\Project\Models\Project;
use App\Modules\Project\Requests\FetchProjectRequest;
use App\Modules\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/** @extends BaseRepository<Project> */
class ProjectRepository extends BaseRepository
{
    public function __construct(Project $model)
    {
        parent::__construct($model);
    }

    /** @return LengthAwarePaginator<Project> */
    public function paginateForUser(User $user, FetchProjectRequest $request): LengthAwarePaginator
    {
        $query = $user->projects()->withCount('tasks');

        $query->when(
            $request->filled('status'),
            fn ($query) => $query->where('projects.status', $request->integer('status')),
        );

        $query->when($request->search(), function ($query, string $search): void {
            $query->where(function ($query) use ($search): void {
                $query
                    ->where('projects.title', 'like', "%{$search}%")
                    ->orWhere('projects.description', 'like', "%{$search}%");
            });
        });

        $sort = $request->sort() ?? 'created_at';

        return $query
            ->orderBy("projects.{$sort}", $request->direction())
            ->paginate($request->perPage());
    }

    public function findForUserOrFail(User $user, string $uuid): Project
    {
        return $user->projects()
            ->withCount('tasks')
            ->where('projects.uuid', $uuid)
            ->firstOrFail();
    }
}
