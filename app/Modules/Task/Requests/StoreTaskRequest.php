<?php

namespace App\Modules\Task\Requests;

use App\Modules\Core\Requests\BaseRequest;
use App\Modules\Task\Enums\TaskPriority;
use App\Modules\Task\Enums\TaskStatus;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends BaseRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['sometimes', Rule::enum(TaskStatus::class)],
            'priority' => ['required', Rule::enum(TaskPriority::class)],
            'due_date' => ['nullable', 'date_format:Y-m-d'],
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
