<?php

declare(strict_types=1);

namespace LaSalle\StudentTeacher\User\RefreshToken\Application\Exception;

use Exception;
use Throwable;

final class RefreshTokenNotFoundException extends Exception
{
    public function __construct($code = 0, Throwable $previous = null)
    {
        parent::__construct(sprintf('Refresh Token was not found'), $code, $previous);
    }
}