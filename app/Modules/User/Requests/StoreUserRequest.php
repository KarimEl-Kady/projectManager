<?php

namespace App\Modules\User\Requests;

use App\Modules\Core\Requests\{BaseRequest};

class StoreUserRequest extends BaseRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            //
        ]);
    }
}