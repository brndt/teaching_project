<?php

declare(strict_types=1);

namespace LaSalle\StudentTeacher\User\Connection\Application\Exception;

use Exception;
use Throwable;

final class ConnectionNotFoundException extends Exception
{
    public function __construct($code = 0, Throwable $previous = null)
    {
        parent::__construct(sprintf('Connection doesn\'t exist'), $code, $previous);
    }
}
