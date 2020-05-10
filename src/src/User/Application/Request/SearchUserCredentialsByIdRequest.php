<?php

declare(strict_types=1);

namespace LaSalle\StudentTeacher\User\Application\Request;

final class SearchUserCredentialsByIdRequest
{
    private string $userId;

    public function __construct(string $userId)
    {
        $this->userId = $userId;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }
}