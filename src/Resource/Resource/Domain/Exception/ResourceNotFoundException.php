<?php

declare(strict_types=1);

namespace LaSalle\StudentTeacher\Resource\Resource\Domain\Exception;

use Exception;
use Throwable;

final class ResourceNotFoundException extends Exception
{
    public function __construct($code = 0, Throwable $previous = null)
    {
        parent::__construct(sprintf('Resource not found'), $code, $previous);
    }
}
