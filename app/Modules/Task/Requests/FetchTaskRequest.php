<?php

namespace App\Modules\Task\Requests;

use App\Modules\Core\Requests\FetchRequest;
use App\Modules\Task\Enums\TaskPriority;
use App\Modules\Task\Enums\TaskStatus;
use Illuminate\Validation\Rule;

class FetchTaskRequest extends FetchRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'status' => ['sometimes', Rule::enum(TaskStatus::class)],
            'priority' => ['sometimes', Rule::enum(TaskPriority::class)],
            'sort' => ['sometimes', Rule::in(['title', 'status', 'priority', 'due_date', 'created_at', 'updated_at'])],
        ]);
    }

    protected function prepareForValidation(): void
    {
        $values = [];

        if ($this->has('status')) {
            $values['status'] = TaskStatus::valueFromLabel($this->input('status'));
        }

        if ($this->has('priority')) {
            $values['priority'] = TaskPriority::valueFromLabel($this->input('priority'));
        }

        $this->merge($values);
    }
}
