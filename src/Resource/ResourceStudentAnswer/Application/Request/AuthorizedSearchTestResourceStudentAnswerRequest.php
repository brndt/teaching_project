<?php

declare(strict_types=1);

namespace LaSalle\StudentTeacher\Resource\ResourceStudentAnswer\Application\Request;

final class AuthorizedSearchTestResourceStudentAnswerRequest
{
    public function __construct(private string $requestAuthorId, private string $resourceId, private string $studentId)
    {
    }

    public function getRequestAuthorId(): string
    {
        return $this->requestAuthorId;
    }

    public function getResourceId(): string
    {
        return $this->resourceId;
    }

    public function getStudentId(): string
    {
        return $this->studentId;
    }
}
