<?php

namespace App\Modules\User\Repositories;

use App\Modules\Core\Repositories\BaseRepository;
use App\Modules\User\Models\User;

/** @extends BaseRepository<User> */
class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }
}