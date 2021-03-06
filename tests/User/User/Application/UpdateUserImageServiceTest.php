<?php

declare(strict_types=1);

namespace Test\LaSalle\StudentTeacher\User\User\Application;

use LaSalle\StudentTeacher\Resource\Course\Domain\Repository\CourseRepository;
use LaSalle\StudentTeacher\Resource\CoursePermission\Domain\Repository\CoursePermissionRepository;
use LaSalle\StudentTeacher\Resource\Unit\Domain\Repository\UnitRepository;
use LaSalle\StudentTeacher\Shared\Application\Exception\PermissionDeniedException;
use LaSalle\StudentTeacher\Shared\Domain\Exception\InvalidUuidException;
use LaSalle\StudentTeacher\Shared\Domain\ValueObject\Uuid;
use LaSalle\StudentTeacher\User\Shared\Application\Exception\UserNotFoundException;
use LaSalle\StudentTeacher\User\User\Application\Request\UpdateUserImageRequest;
use LaSalle\StudentTeacher\User\User\Application\Service\UpdateUserImageService;
use LaSalle\StudentTeacher\User\User\Domain\Aggregate\User;
use LaSalle\StudentTeacher\User\User\Domain\Repository\UserRepository;
use LaSalle\StudentTeacher\User\User\Domain\Service\AuthorizationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Test\LaSalle\StudentTeacher\User\User\Domain\UserBuilder;

final class UpdateUserImageServiceTest extends TestCase
{
    private UpdateUserImageService $updateUserImageService;
    private MockObject $repository;

    public function setUp(): void
    {
        $this->repository = $this->createMock(UserRepository::class);
        $coursePermissionRepository = $this->createMock(CoursePermissionRepository::class);
        $unitRepository = $this->createMock(UnitRepository::class);
        $courseRepository = $this->createMock(CourseRepository::class);
        $authorizationService = new AuthorizationService($coursePermissionRepository, $unitRepository, $courseRepository);
        $this->updateUserImageService = new UpdateUserImageService($this->repository, $authorizationService);
    }

    public function testWhenRequestAuthorIdIsInvalidThenThrowException()
    {
        $this->expectException(InvalidUuidException::class);

        $request = new UpdateUserImageRequest(
            '16bf6c6a-c855-4a36-a3dd-5b9f6d92c753-invalid',
            'cfe849f3-7832-435a-b484-83fabf530794',
            'image.jpg',
        );
        ($this->updateUserImageService)($request);
    }

    public function testWhenRequestAuthorIsNotFoundThenThrowException()
    {
        $this->expectException(UserNotFoundException::class);

        $request = new UpdateUserImageRequest(
            '16bf6c6a-c855-4a36-a3dd-5b9f6d92c753',
            'cfe849f3-7832-435a-b484-83fabf530794',
            'image.jpg',
        );
        $this->repository
            ->expects(self::once())
            ->method('ofId')
            ->with(new Uuid($request->getRequestAuthorId()))
            ->willReturn(null);
        ($this->updateUserImageService)($request);
    }

    public function testWhenUserIdIsInvalidThenThrowException()
    {
        $this->expectException(InvalidUuidException::class);

        $request = new UpdateUserImageRequest(
            '16bf6c6a-c855-4a36-a3dd-5b9f6d92c753',
            'cfe849f3-7832-435a-b484-83fabf530794-invalid',
            'image.jpg',
        );
        $author = (new UserBuilder())
            ->withId(new Uuid($request->getRequestAuthorId()))
            ->build();
        $this->repository
            ->method('ofId')
            ->with($request->getRequestAuthorId())
            ->willReturn($author);
        ($this->updateUserImageService)($request);
    }

    public function testWhenUserIdIsNotFoundThenThrowException()
    {
        $this->expectException(UserNotFoundException::class);

        $request = new UpdateUserImageRequest(
            '16bf6c6a-c855-4a36-a3dd-5b9f6d92c753',
            'cfe849f3-7832-435a-b484-83fabf530794',
            'image.jpg',
        );
        $author = (new UserBuilder())
            ->withId(new Uuid($request->getRequestAuthorId()))
            ->build();
        $this->repository
            ->method('ofId')
            ->withConsecutive([$request->getRequestAuthorId()], [$request->getUserId()])
            ->willReturn($author, null);
        ($this->updateUserImageService)($request);
    }

    public function testWhenRequestAuthorIsNotUserThanThrowException()
    {
        $this->expectException(PermissionDeniedException::class);

        $request = new UpdateUserImageRequest(
            '16bf6c6a-c855-4a36-a3dd-5b9f6d92c753',
            'cfe849f3-7832-435a-b484-83fabf530794',
            'image.jpg',
        );
        $author = (new UserBuilder())->build();
        $user = (new UserBuilder())->build();
        $this->repository
            ->method('ofId')
            ->withConsecutive([$request->getRequestAuthorId()], [$request->getUserId()])
            ->willReturn($author, $user);
        ($this->updateUserImageService)($request);
    }

    public function testWhenRequestIsValidThenUpdateImage()
    {
        $request = new UpdateUserImageRequest(
            '16bf6c6a-c855-4a36-a3dd-5b9f6d92c753',
            '16bf6c6a-c855-4a36-a3dd-5b9f6d92c753',
            'image.jpg',
        );
        $author = (new UserBuilder())
            ->withId(new Uuid($request->getRequestAuthorId()))
            ->build();
        $user = (new UserBuilder())
            ->withId(new Uuid($request->getUserId()))
            ->build();
        $this->repository
            ->method('ofId')
            ->withConsecutive([$request->getRequestAuthorId()], [$request->getUserId()])
            ->willReturn($author, $user);
        $this->repository->expects(self::once())->method('save')->with(
            $this->callback($this->userComparator($user))
        );
        ($this->updateUserImageService)($request);
    }

    private function userComparator(User $userExpected): callable
    {
        return function (User $userActual) use ($userExpected) {
            return $userExpected->getImage() === $userActual->getImage();
        };
    }
}
