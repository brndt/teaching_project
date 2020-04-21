<?php

declare(strict_types=1);

namespace LaSalle\StudentTeacher\User\Application\BasicUserInformation\Search;

use LaSalle\StudentTeacher\Shared\Domain\ValueObject\Uuid;
use LaSalle\StudentTeacher\User\Application\BasicUserInformation\BasicUserInformationResponse;
use LaSalle\StudentTeacher\User\Application\Exception\UserNotFoundException;
use LaSalle\StudentTeacher\User\Domain\UserRepository;

final class SearchBasicUserInformation
{
    private UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(SearchBasicUserInformationRequest $request): BasicUserInformationResponse
    {
        $user = $this->repository->searchById(Uuid::fromString($request->getId()));

        if (null === $user) {
            throw new UserNotFoundException();
        }

        return new BasicUserInformationResponse(
            $user->getId()->getValue(),
            $user->getEmail(),
            $user->getFirstName(),
            $user->getLastName(),
            $user->getRoles()->toPrimitives(),
            $user->getImage(),
            $user->getEducation(),
            $user->getExperience(),
        );
    }
}