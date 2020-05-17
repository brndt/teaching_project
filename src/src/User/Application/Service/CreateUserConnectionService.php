<?php

declare(strict_types=1);

namespace LaSalle\StudentTeacher\User\Application\Service;

use LaSalle\StudentTeacher\User\Application\Request\CreateUserConnectionRequest;
use LaSalle\StudentTeacher\User\Domain\Aggregate\UserConnection;
use LaSalle\StudentTeacher\User\Domain\ValueObject\State\Pended;

final class CreateUserConnectionService extends UserConnectionService
{
    public function __invoke(CreateUserConnectionRequest $request): void
    {
        $authorId = $this->createIdFromPrimitive($request->getRequestAuthorId());
        $requestAuthor = $this->userRepository->ofId($authorId);
        $this->ensureUserExists($requestAuthor);

        $firstUserId = $this->createIdFromPrimitive($request->getFirstUser());
        $firstUser = $this->userRepository->ofId($firstUserId);
        $this->ensureUserExists($firstUser);

        $secondUserId = $this->createIdFromPrimitive($request->getSecondUser());
        $secondUser = $this->userRepository->ofId($secondUserId);
        $this->ensureUserExists($secondUser);

        $this->ensureRequestAuthorIsOneOfUsers($requestAuthor, $firstUser, $secondUser);

        [$student, $teacher] = $this->identifyStudentAndTeacher($firstUser, $secondUser);
        $this->ensureConnectionDoesntExists($student, $teacher);

        $userConnection = new UserConnection($student->getId(), $teacher->getId(), new Pended(), $authorId);

        $this->userConnectionRepository->save($userConnection);
    }
}