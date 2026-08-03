<?php

namespace App\Modules\Task\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Project\Services\ProjectService;
use App\Modules\Task\Requests\FetchTaskRequest;
use App\Modules\Task\Requests\StoreTaskRequest;
use App\Modules\Task\Requests\UpdateTaskRequest;
use App\Modules\Task\Resources\TaskResource;
use App\Modules\Task\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends Controller
{
    public function __construct(
        private TaskService $service,
        private ProjectService $projects,
    ) {}

    public function index(FetchTaskRequest $request, string $project): AnonymousResourceCollection
    {
        $project = $this->projects->findForUserOrFail($request->user(), $project);

        return TaskResource::collection($this->service->listForProject($project, $request));
    }

    public function store(StoreTaskRequest $request, string $project): JsonResponse
    {
        $project = $this->projects->findForUserOrFail($request->user(), $project);

        return (new TaskResource(
            $this->service->createForProject($project, $request->user(), $request->validated()),
        ))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Request $request, string $project, string $task): TaskResource
    {
        $project = $this->projects->findForUserOrFail($request->user(), $project);

        return new TaskResource($this->service->findForProjectOrFail($project, $task));
    }

    public function update(UpdateTaskRequest $request, string $project, string $task): TaskResource
    {
        $project = $this->projects->findForUserOrFail($request->user(), $project);

        return new TaskResource(
            $this->service->updateForProject($project, $request->user(), $task, $request->validated()),
        );
    }

    public function destroy(Request $request, string $project, string $task): Response
    {
        $project = $this->projects->findForUserOrFail($request->user(), $project);
        $this->service->deleteForProject($project, $request->user(), $task);

        return response()->noContent();
    }
}
