<?php

namespace App\Modules\User\Services;

use App\Modules\Core\Services\BaseService;
use App\Modules\User\Repositories\UserRepository;

/** @extends BaseService<UserRepository> */
class UserService extends BaseService
{
    public function __construct(UserRepository $repository)
    {
        parent::__construct($repository);
    }
}