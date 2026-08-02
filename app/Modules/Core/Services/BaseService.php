<?php

namespace App\Modules\Core\Services;

use App\Modules\Core\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @template TRepository of BaseRepository
 */
abstract class BaseService
{
    /** @param TRepository $repository */
    public function __construct(protected BaseRepository $repository) {}

    /** @return Collection<int, Model> */
    public function all(array $columns = ['*']): Collection
    {
        return $this->repository->all($columns);
    }

    /** @return LengthAwarePaginator<Model> */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage, $columns);
    }

    public function findOrFail(int|string $id): Model
    {
        return $this->repository->findOrFail($id);
    }

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): Model
    {
        return DB::transaction(fn (): Model => $this->repository->create($attributes));
    }

    /** @param array<string, mixed> $attributes */
    public function update(Model $model, array $attributes): Model
    {
        return DB::transaction(fn (): Model => $this->repository->update($model, $attributes));
    }

    public function delete(Model $model): bool
    {
        return DB::transaction(fn (): bool => $this->repository->delete($model));
    }
}
