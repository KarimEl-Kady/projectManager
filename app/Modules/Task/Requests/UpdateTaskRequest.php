<?php

namespace App\Modules\Task\Requests;

use App\Modules\Core\Requests\BaseRequest;
use App\Modules\Task\Enums\TaskPriority;
use App\Modules\Task\Enums\TaskStatus;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends BaseRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'status' => ['sometimes', Rule::enum(TaskStatus::class)],
            'priority' => ['sometimes', Rule::enum(TaskPriority::class)],
            'due_date' => ['sometimes', 'nullable', 'date_format:Y-m-d'],
        ];
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
