<?php

declare(strict_types=1);

namespace LaSalle\StudentTeacher\User\User\Domain\Service;

use LaSalle\StudentTeacher\Shared\Domain\ValueObject\Uuid;
use LaSalle\StudentTeacher\User\User\Application\Exception\EmailAlreadyExistsException;
use LaSalle\StudentTeacher\User\Shared\Application\Exception\UserNotFoundException;
use LaSalle\StudentTeacher\User\User\Domain\Aggregate\User;
use LaSalle\StudentTeacher\User\User\Domain\Repository\UserRepository;
use LaSalle\StudentTeacher\User\User\Domain\ValueObject\Email;

final class UserService
{
    public function __construct(private UserRepository $repository)
    {
    }

    public function findUser(Uuid $id): User
    {
        $user = $this->repository->ofId($id);
        if (null === $user) {
            throw new UserNotFoundException();
        }
        return $user;
    }

    public function findUserByEmail(Email $email): User
    {
        $user = $this->repository->ofEmail($email);
        if (null === $user) {
            throw new UserNotFoundException();
        }
        return $user;
    }

    public function ensureNewEmailIsAvailable(Email $newEmail, Email $oldEmail): void
    {
        $userWithNewEmail = $this->repository->ofEmail($newEmail);
        if (null !== $userWithNewEmail && false === $newEmail->equalsTo($oldEmail)) {
            throw new EmailAlreadyExistsException();
        }
    }

    public function ensureUserDoesntExistByEmail(Email $email): void
    {
        if (null !== $this->repository->ofEmail($email)) {
            throw new EmailAlreadyExistsException();
        }
    }
}
