<?php

namespace App\Modules\User\Requests;

use App\Modules\Core\Requests\{FetchRequest};

class FetchUserRequest extends FetchRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            //
        ]);
    }
}