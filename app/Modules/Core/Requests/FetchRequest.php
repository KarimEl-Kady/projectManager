<?php

namespace App\Modules\Core\Requests;

use Illuminate\Validation\Rule;

class FetchRequest extends BaseRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            'sort' => ['sometimes', 'nullable', 'string', 'max:100'],
            'direction' => ['sometimes', Rule::in(['asc', 'desc'])],
        ];
    }

    public function perPage(int $default = 15): int
    {
        return min(max($this->integer('per_page', $default), 1), 100);
    }

    public function search(): ?string
    {
        $search = $this->string('search')->trim()->toString();

        return $search !== '' ? $search : null;
    }

    public function sort(): ?string
    {
        $sort = $this->string('sort')->trim()->toString();

        return $sort !== '' ? $sort : null;
    }

    public function direction(): string
    {
        return $this->string('direction', 'asc')->lower()->toString() === 'desc' ? 'desc' : 'asc';
    }
}
