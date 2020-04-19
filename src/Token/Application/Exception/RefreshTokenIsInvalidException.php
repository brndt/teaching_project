<?php

declare(strict_types=1);

namespace LaSalle\StudentTeacher\Token\Application\Exception;

use Exception;
use Throwable;

final class RefreshTokenIsInvalidException extends Exception
{
    public function __construct($code = 0, Throwable $previous = null)
    {
        parent::__construct(sprintf('Refresh Token is invalid or expired'), $code, $previous);
    }
}