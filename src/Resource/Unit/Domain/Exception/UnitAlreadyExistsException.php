<?php

declare(strict_types=1);

namespace LaSalle\StudentTeacher\Resource\Unit\Domain\Exception;

use Exception;
use Throwable;

final class UnitAlreadyExistsException extends Exception
{
    public function __construct($code = 0, Throwable $previous = null)
    {
        parent::__construct(sprintf('Unit already exists'), $code, $previous);
    }
}
