<?php

declare(strict_types=1);

namespace LaSalle\StudentTeacher\User\User\Application\Exception;

use Exception;
use Throwable;

final class RolesOfUsersEqualException extends Exception
{
    public function __construct($code = 0, Throwable $previous = null)
    {
        parent::__construct(sprintf('Roles of users can not be equal'), $code, $previous);
    }
}