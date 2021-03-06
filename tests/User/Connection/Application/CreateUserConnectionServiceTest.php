<?php

declare(strict_types=1);

namespace Test\LaSalle\StudentTeacher\User\Connection\Application;

use LaSalle\StudentTeacher\Shared\Application\Exception\PermissionDeniedException;
use LaSalle\StudentTeacher\Shared\Domain\Exception\InvalidUuidException;
use LaSalle\StudentTeacher\Shared\Domain\ValueObject\Uuid;
use LaSalle\StudentTeacher\User\Connection\Application\Exception\ConnectionAlreadyExistsException;
use LaSalle\StudentTeacher\User\User\Application\Exception\RolesOfUsersEqualException;
use LaSalle\StudentTeacher\User\Shared\Application\Exception\UserNotFoundException;
use LaSalle\StudentTeacher\User\Shared\Application\Exception\UsersAreEqualException;
use LaSalle\StudentTeacher\User\Connection\Application\Request\CreateUserConnectionRequest;
use LaSalle\StudentTeacher\User\Connection\Application\Service\CreateUserConnectionService;
use LaSalle\StudentTeacher\User\Connection\Domain\Aggregate\UserConnection;
use LaSalle\StudentTeacher\User\Connection\Domain\Repository\UserConnectionRepository;
use LaSalle\StudentTeacher\User\User\Domain\Repository\UserRepository;
use LaSalle\StudentTeacher\User\User\Domain\Service\AuthorizationService;
use LaSalle\StudentTeacher\User\User\Domain\ValueObject\Role;
use LaSalle\StudentTeacher\User\User\Domain\ValueObject\Roles;
use LaSalle\StudentTeacher\Shared\Domain\ValueObject\State\Pended;
use LaSalle\StudentTeacher\Shared\Domain\ValueObject\State\StateFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Test\LaSalle\StudentTeacher\User\User\Domain\UserBuilder;

final class CreateUserConnectionServiceTest extends TestCase
{
    private CreateUserConnectionService $createUserConnectionService;
    protected MockObject $userConnectionRepository;
    protected MockObject $userRepository;
    protected MockObject $stateFactory;

    public function setUp(): void
    {
        $this->userConnectionRepository = $this->createMock(UserConnectionRepository::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->stateFactory = $this->createMock(StateFactory::class);
        $authorizationService = $this->createMock(AuthorizationService::class);

        $this->createUserConnectionService = new CreateUserConnectionService(
            $this->userConnectionRepository,
            $this->userRepository,
            $authorizationService
        );
    }

    public function testWhenRequestAuthorIdIsInvalidThenThrowException()
    {
        $this->expectException(InvalidUuidException::class);

        $request = new CreateUserConnectionRequest(
            '48d34c63-6bba-4c72-a461-8aac1fd7d138-invalid',
            'cfe849f3-7832-435a-b484-83fabf530794',
            '48d34c63-6bba-4c72-a461-8aac1fd7d138'
        );

        ($this->createUserConnectionService)($request);
    }

    public function testWhenRequestAuthorIsNotFoundThenThrowException()
    {
        $request = new CreateUserConnectionRequest(
            '48d34c63-6bba-4c72-a461-8aac1fd7d138',
            'cfe849f3-7832-435a-b484-83fabf530794',
            '48d34c63-6bba-4c72-a461-8aac1fd7d138'
        );

        $this->expectException(UserNotFoundException::class);
        $this->userRepository->expects(self::once())->method('ofId')->with($request->getRequestAuthorId())->willReturn(
            null
        );
        ($this->createUserConnectionService)($request);
    }

    public function testWhenFirstUserIdIsInvalidThenThrowException()
    {
        $this->expectException(InvalidUuidException::class);

        $request = new CreateUserConnectionRequest(
            '48d34c63-6bba-4c72-a461-8aac1fd7d138',
            'cfe849f3-7832-435a-b484-83fabf530794-invalid',
            '48d34c63-6bba-4c72-a461-8aac1fd7d138'
        );
        $author = (new UserBuilder())
            ->withId(new Uuid($request->getRequestAuthorId()))
            ->build();

        $this->userRepository
            ->method('ofId')
            ->with($request->getRequestAuthorId())
            ->willReturn($author);

        ($this->createUserConnectionService)($request);
    }

    public function testWhenFirstUserIsNotFoundThenThrowException()
    {
        $request = new CreateUserConnectionRequest(
            '48d34c63-6bba-4c72-a461-8aac1fd7d138',
            'cfe849f3-7832-435a-b484-83fabf530794',
            '48d34c63-6bba-4c72-a461-8aac1fd7d138'
        );
        $author = (new UserBuilder())
            ->withId(new Uuid($request->getRequestAuthorId()))
            ->build();

        $this->expectException(UserNotFoundException::class);

        $this->userRepository
            ->method('ofId')
            ->withConsecutive([$request->getRequestAuthorId()], [$request->getFirstUser()])
            ->willReturn($author, null);

        ($this->createUserConnectionService)($request);
    }

    public function testWhenSecondUserIdIsInvalidThenThrowException()
    {
        $this->expectException(InvalidUuidException::class);

        $request = new CreateUserConnectionRequest(
            '48d34c63-6bba-4c72-a461-8aac1fd7d138',
            'cfe849f3-7832-435a-b484-83fabf530794',
            '48d34c63-6bba-4c72-a461-8aac1fd7d138-invalid'
        );

        $author = (new UserBuilder())
            ->withId(new Uuid($request->getRequestAuthorId()))
            ->build();
        $firstUser = (new UserBuilder())
            ->withId(new Uuid($request->getFirstUser()))
            ->build();

        $this->userRepository
            ->method('ofId')
            ->withConsecutive([$request->getRequestAuthorId()], [$request->getFirstUser()])
            ->willReturn($author, $firstUser);

        ($this->createUserConnectionService)($request);
    }

    public function testWhenSecondUserIsNotFoundThenThrowException()
    {
        $request = new CreateUserConnectionRequest(
            '48d34c63-6bba-4c72-a461-8aac1fd7d138',
            'cfe849f3-7832-435a-b484-83fabf530794',
            '16bf6c6a-c855-4a36-a3dd-5b9f6d92c753'
        );
        $author = (new UserBuilder())
            ->withId(new Uuid($request->getRequestAuthorId()))
            ->build();
        $firstUser = (new UserBuilder())
            ->withId(new Uuid($request->getFirstUser()))
            ->build();

        $this->expectException(UserNotFoundException::class);

        $this->userRepository
            ->method('ofId')
            ->withConsecutive([$request->getRequestAuthorId()], [$request->getFirstUser()], [$request->getSecondUser()])
            ->willReturn($author, $firstUser, null);

        ($this->createUserConnectionService)($request);
    }

    public function testWhenUsersAreEqualThenThrowException()
    {
        $request = new CreateUserConnectionRequest(
            '48d34c63-6bba-4c72-a461-8aac1fd7d138',
            '48d34c63-6bba-4c72-a461-8aac1fd7d138',
            '48d34c63-6bba-4c72-a461-8aac1fd7d138'
        );
        $author = (new UserBuilder())
            ->withId(new Uuid($request->getRequestAuthorId()))
            ->build();
        $firstUser = (new UserBuilder())
            ->withId(new Uuid($request->getFirstUser()))
            ->build();
        $secondUser = (new UserBuilder())
            ->withId(new Uuid($request->getSecondUser()))
            ->build();

        $this->expectException(UsersAreEqualException::class);

        $this->userRepository
            ->method('ofId')
            ->withConsecutive([$request->getRequestAuthorId()], [$request->getFirstUser()], [$request->getSecondUser()])
            ->willReturn($author, $firstUser, $secondUser);

        ($this->createUserConnectionService)($request);
    }

    public function testWhenUsersRolesAreEqualThenThrowException()
    {
        $request = new CreateUserConnectionRequest(
            '48d34c63-6bba-4c72-a461-8aac1fd7d138',
            'cfe849f3-7832-435a-b484-83fabf530794',
            '48d34c63-6bba-4c72-a461-8aac1fd7d138'
        );

        $author = (new UserBuilder())
            ->withId(new Uuid($request->getRequestAuthorId()))
            ->build();
        $firstUser = (new UserBuilder())
            ->withId(new Uuid($request->getFirstUser()))
            ->withRoles(Roles::fromArrayOfPrimitives([Role::TEACHER]))
            ->build();
        $secondUser = (new UserBuilder())
            ->withId(new Uuid($request->getSecondUser()))
            ->withRoles(Roles::fromArrayOfPrimitives([Role::TEACHER]))
            ->build();

        $this->expectException(RolesOfUsersEqualException::class);

        $this->userRepository
            ->method('ofId')
            ->withConsecutive([$request->getRequestAuthorId()], [$request->getFirstUser()], [$request->getSecondUser()])
            ->willReturn($author, $firstUser, $secondUser);

        ($this->createUserConnectionService)($request);
    }

    public function testWhenUsersRolesAreNotStudentOrTeacherThenThrowException()
    {
        $request = new CreateUserConnectionRequest(
            '48d34c63-6bba-4c72-a461-8aac1fd7d138',
            'cfe849f3-7832-435a-b484-83fabf530794',
            '48d34c63-6bba-4c72-a461-8aac1fd7d138'
        );

        $author = (new UserBuilder())
            ->withId(new Uuid($request->getRequestAuthorId()))
            ->build();
        $firstUser = (new UserBuilder())
            ->withId(new Uuid($request->getFirstUser()))
            ->withRoles(Roles::fromArrayOfPrimitives([Role::ADMIN]))
            ->build();
        $secondUser = (new UserBuilder())
            ->withId(new Uuid($request->getSecondUser()))
            ->withRoles(Roles::fromArrayOfPrimitives([Role::TEACHER]))
            ->build();

        $this->expectException(PermissionDeniedException::class);

        $this->userRepository
            ->method('ofId')
            ->withConsecutive([$request->getRequestAuthorId()], [$request->getFirstUser()], [$request->getSecondUser()])
            ->willReturn($author, $firstUser, $secondUser);

        ($this->createUserConnectionService)($request);
    }

    public function testWhenConnectionAlreadyExistsThenThrowException()
    {
        $request = new CreateUserConnectionRequest(
            '48d34c63-6bba-4c72-a461-8aac1fd7d138',
            'cfe849f3-7832-435a-b484-83fabf530794',
            '48d34c63-6bba-4c72-a461-8aac1fd7d138'
        );

        $author = (new UserBuilder())
            ->withId(new Uuid($request->getRequestAuthorId()))
            ->build();
        $firstUser = (new UserBuilder())
            ->withId(new Uuid($request->getFirstUser()))
            ->withRoles(Roles::fromArrayOfPrimitives([Role::STUDENT]))
            ->build();
        $secondUser = (new UserBuilder())
            ->withId(new Uuid($request->getSecondUser()))
            ->withRoles(Roles::fromArrayOfPrimitives([Role::TEACHER]))
            ->build();

        $userConnection = new UserConnection($firstUser->getId(), $secondUser->getId(), new Pended(), $author->getId());

        $this->expectException(ConnectionAlreadyExistsException::class);

        $this->userRepository
            ->method('ofId')
            ->withConsecutive([$request->getRequestAuthorId()], [$request->getFirstUser()], [$request->getSecondUser()])
            ->willReturn($author, $firstUser, $secondUser);

        $this->userConnectionRepository->expects(self::once())->method('ofId')->willReturn($userConnection);

        ($this->createUserConnectionService)($request);
    }

    public function testWhenRequestIsValidThenCreateUserConnection()
    {
        $request = new CreateUserConnectionRequest(
            '48d34c63-6bba-4c72-a461-8aac1fd7d138',
            'cfe849f3-7832-435a-b484-83fabf530794',
            '48d34c63-6bba-4c72-a461-8aac1fd7d138'
        );

        $author = (new UserBuilder())
            ->withId(new Uuid($request->getRequestAuthorId()))
            ->build();
        $firstUser = (new UserBuilder())
            ->withId(new Uuid($request->getFirstUser()))
            ->withRoles(Roles::fromArrayOfPrimitives([Role::STUDENT]))
            ->build();
        $secondUser = (new UserBuilder())
            ->withId(new Uuid($request->getSecondUser()))
            ->withRoles(Roles::fromArrayOfPrimitives([Role::TEACHER]))
            ->build();

        $userConnection = new UserConnection($firstUser->getId(), $secondUser->getId(), new Pended(), $author->getId());

        $this->userRepository
            ->method('ofId')
            ->withConsecutive([$request->getRequestAuthorId()], [$request->getFirstUser()], [$request->getSecondUser()])
            ->willReturn($author, $firstUser, $secondUser);

        $this->userConnectionRepository
            ->expects(self::once())
            ->method('ofId')
            ->willReturn(null);

        $this->userConnectionRepository
            ->expects(self::once())
            ->method('save')
            ->with($userConnection);

        ($this->createUserConnectionService)($request);
    }
}
