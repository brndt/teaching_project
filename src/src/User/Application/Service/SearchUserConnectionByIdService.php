<?php

declare(strict_types=1);

namespace LaSalle\StudentTeacher\User\Application\Service;

use LaSalle\StudentTeacher\Shared\Domain\ValueObject\Uuid;
use LaSalle\StudentTeacher\User\Application\Request\SearchUserConnectionByCriteriaRequest;
use LaSalle\StudentTeacher\User\Application\Response\UserConnectionResponse;
use LaSalle\StudentTeacher\User\Domain\Aggregate\UserConnection;
use LaSalle\StudentTeacher\User\Domain\Repository\UserConnectionRepository;
use LaSalle\StudentTeacher\User\Domain\Repository\UserRepository;
use LaSalle\StudentTeacher\User\Domain\Service\AuthorizationService;
use LaSalle\StudentTeacher\User\Domain\Service\UserConnectionService;
use LaSalle\StudentTeacher\User\Domain\Service\UserService;
use LaSalle\StudentTeacher\User\Domain\ValueObject\Role;

final class SearchUserConnectionByIdService
{
    private UserRepository $userRepository;
    private UserConnectionRepository $userConnectionRepository;
    private UserService $userService;
    private AuthorizationService $authorizationService;
    private UserConnectionService $userConnectionService;

    public function __construct(
        UserConnectionRepository $userConnectionRepository,
        UserRepository $userRepository
    ) {
        $this->userRepository = $userRepository;
        $this->userConnectionRepository = $userConnectionRepository;
        $this->userService = new UserService($this->userRepository);
        $this->authorizationService = new AuthorizationService();
        $this->userConnectionService = new UserConnectionService($this->userConnectionRepository);
    }

    public function __invoke(SearchUserConnectionByCriteriaRequest $request)
    {
        $authorId = new Uuid($request->getRequestAuthorId());
        $requestAuthor = $this->userService->findUser($authorId);

        $userId = new Uuid($request->getUserId());
        $user = $this->userService->findUser($userId);

        $friendId = new Uuid($request->getFriendId());
        $friend = $this->userService->findUser($friendId);

        $this->authorizationService->ensureRequestAuthorHasPermissionsToUserConnection($requestAuthor, $user);

        [$student, $teacher] = $this->userConnectionService->identifyStudentAndTeacher($user, $friend);

        $connection = $this->userConnectionService->findUserConnection($student, $teacher);

        if (true === $user->isInRole(new Role(Role::STUDENT))) {
            return $this->buildStudentResponse($connection);
        }

        return $this->buildTeacherResponse($connection);
    }

    private function buildStudentResponse(UserConnection $connection): UserConnectionResponse
    {
        return new UserConnectionResponse(
            $connection->getStudentId()->toString(),
            $connection->getTeacherId()->toString(),
            (string)$connection->getState(),
            $connection->getSpecifierId()->toString()
        );
    }

    private function buildTeacherResponse(UserConnection $connection): UserConnectionResponse
    {
        return new UserConnectionResponse(
            $connection->getStudentId()->toString(),
            $connection->getTeacherId()->toString(),
            (string)$connection->getState(),
            $connection->getSpecifierId()->toString()
        );
    }
}
