<?php

namespace App\Modules\Project\Requests;

use App\Modules\Core\Requests\FetchRequest;
use App\Modules\Project\Enums\ProjectStatus;
use Illuminate\Validation\Rule;

class FetchProjectRequest extends FetchRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'status' => ['sometimes', Rule::enum(ProjectStatus::class)],
            'sort' => ['sometimes', Rule::in(['title', 'status', 'created_at', 'updated_at'])],
        ]);
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('status')) {
            $this->merge(['status' => ProjectStatus::valueFromLabel($this->input('status'))]);
        }
    }
}
