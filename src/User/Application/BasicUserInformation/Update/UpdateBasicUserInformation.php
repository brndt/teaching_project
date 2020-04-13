<?php

declare(strict_types=1);

namespace LaSalle\StudentTeacher\User\Application\BasicUserInformation\Update;

use LaSalle\StudentTeacher\User\Domain\User;
use LaSalle\StudentTeacher\User\Domain\UserRepository;

final class UpdateBasicUserInformation
{
    private UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(UpdateBasicUserInformationRequest $request): void
    {
        $user = new User();
        $user->setId($request->getId());
        $user->setEmail($request->getEmail());
        $user->setFirstName($request->getFirstName());
        $user->setLastName($request->getLastName());
        $user->setImage($request->getImage());
        $user->setEducation($request->getEducation());
        $user->setExperience($request->getExperience());

        $this->repository->updateBasicInformation($user);
    }
}