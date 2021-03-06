<?php

declare(strict_types=1);

namespace LaSalle\StudentTeacher\User\Shared\Application\Exception;

use Exception;
use Throwable;

final class UserNotFoundException extends Exception
{
    public function __construct($code = 0, Throwable $previous = null)
    {
        parent::__construct(sprintf('User was not found'), $code, $previous);
    }
}