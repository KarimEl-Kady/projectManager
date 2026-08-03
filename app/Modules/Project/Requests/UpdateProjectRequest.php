<?php

namespace App\Modules\Project\Requests;

use App\Modules\Core\Requests\BaseRequest;
use App\Modules\Project\Enums\ProjectStatus;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends BaseRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'status' => ['sometimes', Rule::enum(ProjectStatus::class)],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('status')) {
            $this->merge(['status' => ProjectStatus::valueFromLabel($this->input('status'))]);
        }
    }
}
