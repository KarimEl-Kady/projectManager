<?php

namespace App\Modules\Task\Models;

use App\Modules\Core\Models\Concerns\HasUuid;
use App\Modules\Project\Models\Project;
use App\Modules\Task\Enums\TaskPriority;
use App\Modules\Task\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'project_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
    ];

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return HasMany<TaskNote, $this> */
    public function notes(): HasMany
    {
        return $this->hasMany(TaskNote::class);
    }

    /** @return HasMany<TaskActivity, $this> */
    public function activities(): HasMany
    {
        return $this->hasMany(TaskActivity::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => TaskStatus::class,
            'priority' => TaskPriority::class,
            'due_date' => 'date',
            'overdue_notified_at' => 'datetime',
        ];
    }
}
