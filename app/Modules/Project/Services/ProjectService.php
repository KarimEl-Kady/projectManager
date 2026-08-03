<?php

namespace App\Modules\Project\Services;

use App\Modules\Core\Services\BaseService;
use App\Modules\Project\Models\Project;
use App\Modules\Project\Repositories\ProjectRepository;
use App\Modules\Project\Requests\FetchProjectRequest;
use App\Modules\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/** @extends BaseService<ProjectRepository> */
class ProjectService extends BaseService
{
    public function __construct(ProjectRepository $repository)
    {
        parent::__construct($repository);
    }

    /** @return LengthAwarePaginator<Project> */
    public function listForUser(User $user, FetchProjectRequest $request): LengthAwarePaginator
    {
        return $this->projectRepository()->paginateForUser($user, $request);
    }

    public function findForUserOrFail(User $user, string $uuid): Project
    {
        return $this->projectRepository()->findForUserOrFail($user, $uuid);
    }

    /** @param array<string, mixed> $attributes */
    public function createForUser(User $user, array $attributes): Project
    {
        return DB::transaction(function () use ($user, $attributes): Project {
            /** @var Project $project */
            $project = $this->projectRepository()->create($attributes);
            $project->users()->attach($user);

            return $project->loadCount('tasks');
        });
    }

    /** @param array<string, mixed> $attributes */
    public function updateForUser(User $user, string $uuid, array $attributes): Project
    {
        return DB::transaction(function () use ($user, $uuid, $attributes): Project {
            $project = $this->findForUserOrFail($user, $uuid);

            /** @var Project $updated */
            $updated = $this->projectRepository()->update($project, $attributes);

            return $updated->loadCount('tasks');
        });
    }

    public function deleteForUser(User $user, string $uuid): void
    {
        DB::transaction(function () use ($user, $uuid): void {
            $project = $this->findForUserOrFail($user, $uuid);
            $project->tasks()->delete();
            $this->projectRepository()->delete($project);
        });
    }

    private function projectRepository(): ProjectRepository
    {
        /** @var ProjectRepository */
        return $this->repository;
    }
}
