<?php

namespace App\Modules\Project\Models;

use App\Modules\Core\Models\Concerns\HasUuid;
use App\Modules\Project\Enums\ProjectStatus;
use App\Modules\Task\Models\Task;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'title',
        'description',
        'status',
    ];

    /** @return BelongsToMany<User, $this> */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_users')->withTimestamps();
    }

    /** @return HasMany<Task, $this> */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
        ];
    }
}
