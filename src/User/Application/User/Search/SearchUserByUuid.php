<?php

declare(strict_types=1);

namespace LaSalle\StudentTeacher\User\Application\User\Search;

use LaSalle\StudentTeacher\User\Application\User\UserResponse;
use LaSalle\StudentTeacher\User\Domain\UserRepository;

final class SearchUserByUuid
{
    private UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(SearchUserByUuidRequest $request): ?UserResponse
    {
        $user = $this->repository->searchByUuid($request->getUuid());

        if (null === $user) {
            return null;
        }
        return new UserResponse(
            $user->getId(),
            $user->getUuid(),
            $user->getEmail(),
            $user->getPassword(),
            $user->getFirstName(),
            $user->getLastName(),
            $user->getRoles()->toPrimitives(),
            $user->getImage(),
            $user->getEducation(),
            $user->getExperience(),
            $user->getCreated()->format('Y-m-d H:i:s')
        );
    }
}