<?php

declare(strict_types=1);

namespace LaSalle\StudentTeacher\User\Application\Service;

use LaSalle\StudentTeacher\Shared\Application\Exception\InvalidArgumentValidationException;
use LaSalle\StudentTeacher\Shared\Domain\Exception\InvalidUuidException;
use LaSalle\StudentTeacher\Shared\Domain\ValueObject\Uuid;
use LaSalle\StudentTeacher\Token\Domain\ValueObject\Token;
use LaSalle\StudentTeacher\User\Application\Exception\ConfirmationTokenNotFoundException;
use LaSalle\StudentTeacher\User\Application\Exception\IncorrectConfirmationTokenException;
use LaSalle\StudentTeacher\User\Application\Exception\UserNotFoundException;
use LaSalle\StudentTeacher\User\Application\Request\ConfirmUserEmailRequest;
use LaSalle\StudentTeacher\User\Domain\Aggregate\User;
use LaSalle\StudentTeacher\User\Domain\Repository\UserRepository;

final class ConfirmUserEmail
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function __invoke(ConfirmUserEmailRequest $request)
    {
        $user = $this->userRepository->ofId($this->createIdFromPrimitive($request->getUserId()));
        $this->checkIfUserExists($user);
        $this->checkIfConfirmationTokenExists($user->getConfirmationToken());

        $this->validateConfirmationTokenFromUser($request->getConfirmationToken(), $user->getConfirmationToken()->toString());

        $user->setConfirmationToken(null);
        $user->setEnabled(true);

        $this->userRepository->save($user);
    }

    private function createIdFromPrimitive(string $uuid): Uuid
    {
        try {
            return new Uuid($uuid);
        } catch (InvalidUuidException $error) {
            throw new InvalidArgumentValidationException($error->getMessage());
        }
    }

    private function checkIfUserExists(?User $user): void
    {
        if (null === $user) {
            throw new UserNotFoundException();
        }
    }

    private function checkIfConfirmationTokenExists(?Token $tokenFromUser)
    {
        if (null == $tokenFromUser) {
            throw new ConfirmationTokenNotFoundException();
        }
    }

    private function validateConfirmationTokenFromUser(string $tokenFromRequest, string $tokenFromUser)
    {
        if ($tokenFromRequest !== $tokenFromUser) {
            throw new IncorrectConfirmationTokenException();
        }
    }
}