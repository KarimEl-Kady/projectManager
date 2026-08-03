<?php

namespace App\Modules\Project\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Project\Requests\FetchProjectRequest;
use App\Modules\Project\Requests\StoreProjectRequest;
use App\Modules\Project\Requests\UpdateProjectRequest;
use App\Modules\Project\Resources\ProjectResource;
use App\Modules\Project\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class ProjectController extends Controller
{
    public function __construct(private ProjectService $service) {}

    public function index(FetchProjectRequest $request): AnonymousResourceCollection
    {
        return ProjectResource::collection($this->service->listForUser($request->user(), $request));
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        return (new ProjectResource(
            $this->service->createForUser($request->user(), $request->validated()),
        ))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Request $request, string $project): ProjectResource
    {
        return new ProjectResource($this->service->findForUserOrFail($request->user(), $project));
    }

    public function update(UpdateProjectRequest $request, string $project): ProjectResource
    {
        return new ProjectResource(
            $this->service->updateForUser($request->user(), $project, $request->validated()),
        );
    }

    public function destroy(Request $request, string $project): Response
    {
        $this->service->deleteForUser($request->user(), $project);

        return response()->noContent();
    }
}
