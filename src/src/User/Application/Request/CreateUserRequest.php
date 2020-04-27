<?php

declare(strict_types=1);

namespace LaSalle\StudentTeacher\User\Application\Request;

final class CreateUserRequest
{
    private string $email;
    private string $password;
    private string $firstName;
    private string $lastName;
    private array $roles;
    private \DateTimeImmutable $created;

    public function __construct(string $email, string $password, string $firstName, string $lastName, array $roles, \DateTimeImmutable $created)
    {
        $this->email = $email;
        $this->password = $password;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->roles = $roles;
        $this->created = $created;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getCreated(): \DateTimeImmutable
    {
        return $this->created;
    }
}