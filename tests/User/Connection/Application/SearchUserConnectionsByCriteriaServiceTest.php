<?php

declare(strict_types=1);

namespace Test\LaSalle\StudentTeacher\User\Connection\Application;

use LaSalle\StudentTeacher\Resource\Course\Domain\Repository\CourseRepository;
use LaSalle\StudentTeacher\Resource\CoursePermission\Domain\Repository\CoursePermissionRepository;
use LaSalle\StudentTeacher\Resource\Unit\Domain\Repository\UnitRepository;
use LaSalle\StudentTeacher\Shared\Application\Exception\PermissionDeniedException;
use LaSalle\StudentTeacher\Shared\Domain\Exception\InvalidUuidException;
use LaSalle\StudentTeacher\Shared\Domain\ValueObject\Uuid;
use LaSalle\StudentTeacher\User\Shared\Application\Exception\UserNotFoundException;
use LaSalle\StudentTeacher\User\Connection\Application\Request\SearchUserConnectionsByCriteriaRequest;
use LaSalle\StudentTeacher\User\Connection\Application\Response\UserConnectionCollectionResponse;
use LaSalle\StudentTeacher\User\Connection\Application\Response\UserConnectionResponse;
use LaSalle\StudentTeacher\User\Connection\Application\Service\SearchUserConnectionsByCriteriaService;
use LaSalle\StudentTeacher\User\Connection\Domain\Aggregate\UserConnection;
use LaSalle\StudentTeacher\User\Connection\Domain\Repository\UserConnectionRepository;
use LaSalle\StudentTeacher\User\User\Domain\Repository\UserRepository;
use LaSalle\StudentTeacher\User\User\Domain\Service\AuthorizationService;
use LaSalle\StudentTeacher\User\User\Domain\ValueObject\Role;
use LaSalle\StudentTeacher\User\User\Domain\ValueObject\Roles;
use LaSalle\StudentTeacher\Shared\Domain\ValueObject\State\Pended;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Test\LaSalle\StudentTeacher\User\User\Domain\UserBuilder;

final class SearchUserConnectionsByCriteriaServiceTest extends TestCase
{
    private SearchUserConnectionsByCriteriaService $searchUserConnectionService;
    protected MockObject $userConnectionRepository;
    protected MockObject $userRepository;
    protected MockObject $stateFactory;

    public function setUp(): void
    {
        $this->userConnectionRepository = $this->createMock(UserConnectionRepository::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $coursePermissionRepository = $this->createMock(CoursePermissionRepository::class);
        $unitRepository = $this->createMock(UnitRepository::class);
        $courseRepository = $this->createMock(CourseRepository::class);
        $authorizationService = new AuthorizationService($coursePermissionRepository, $unitRepository, $courseRepository);

        $this->searchUserConnectionService = new SearchUserConnectionsByCriteriaService(
            $this->userConnectionRepository,
            $this->userRepository,
            $authorizationService
        );
    }

    public function testWhenRequestAuthorIdIsInvalidThenThrowException()
    {
        $this->expectException(InvalidUuidException::class);

        $request = new SearchUserConnectionsByCriteriaRequest(
            '48d34c63-6bba-4c72-a461-8aac1fd7d138-invalid',
            'cfe849f3-7832-435a-b484-83fabf530794',
            null,
            null,
            null,
            null,
            null
        );

        ($this->searchUserConnectionService)($request);
    }

    public function testWhenRequestAuthorIsNotFoundThenThrowException()
    {
        $request = new SearchUserConnectionsByCriteriaRequest(
            '48d34c63-6bba-4c72-a461-8aac1fd7d138',
            'cfe849f3-7832-435a-b484-83fabf530794',
            null,
            null,
            null,
            null,
            null
        );

        $this->expectException(UserNotFoundException::class);
        $this->userRepository
            ->expects(self::once())
            ->method('ofId')
            ->with($request->getRequestAuthorId())
            ->willReturn(null);
        ($this->searchUserConnectionService)($request);
    }

    public function testWhenFirstUserIdIsInvalidThenThrowException()
    {
        $this->expectException(InvalidUuidException::class);

        $request = new SearchUserConnectionsByCriteriaRequest(
            '48d34c63-6bba-4c72-a461-8aac1fd7d138',
            'cfe849f3-7832-435a-b484-83fabf530794-invalid',
            null,
            null,
            null,
            null,
            null
        );
        $author = (new UserBuilder())
            ->withId(new Uuid($request->getRequestAuthorId()))
            ->build();

        $this->userRepository
            ->method('ofId')
            ->with($request->getRequestAuthorId())
            ->willReturn($author);

        ($this->searchUserConnectionService)($request);
    }

    public function testWhenFirstUserIsNotFoundThenThrowException()
    {
        $request = new SearchUserConnectionsByCriteriaRequest(
            '48d34c63-6bba-4c72-a461-8aac1fd7d138',
            'cfe849f3-7832-435a-b484-83fabf530794',
            null,
            null,
            null,
            null,
            null
        );
        $author = (new UserBuilder())
            ->withId(new Uuid($request->getRequestAuthorId()))
            ->build();

        $this->expectException(UserNotFoundException::class);

        $this->userRepository
            ->method('ofId')
            ->withConsecutive([$request->getRequestAuthorId()], [$request->getUserId()])
            ->willReturn($author, null);

        ($this->searchUserConnectionService)($request);
    }

    public function testWhenRequestAuthorHasntPermissionsThenThrowException()
    {
        $request = new SearchUserConnectionsByCriteriaRequest(
            '48d34c63-6bba-4c72-a461-8aac1fd7d138',
            'cfe849f3-7832-435a-b484-83fabf530794',
            null,
            null,
            null,
            null,
            null
        );

        $author = (new UserBuilder())
            ->withId(new Uuid($request->getRequestAuthorId()))
            ->withRoles(Roles::fromArrayOfPrimitives([Role::TEACHER]))
            ->build();
        $user = (new UserBuilder())
            ->withId(new Uuid($request->getUserId()))
            ->build();

        $this->expectException(PermissionDeniedException::class);

        $this->userRepository
            ->method('ofId')
            ->withConsecutive([$request->getRequestAuthorId()], [$request->getUserId()])
            ->willReturn($author, $user);

        ($this->searchUserConnectionService)($request);
    }

    public function testWhenConnectionsAreNotFoundThenReturnEmptyArray()
    {
        $request = new SearchUserConnectionsByCriteriaRequest(
            '48d34c63-6bba-4c72-a461-8aac1fd7d138',
            '48d34c63-6bba-4c72-a461-8aac1fd7d138',
            null,
            null,
            null,
            null,
            null
        );
        $expectedUserConnectionCollectionResponse = new UserConnectionCollectionResponse(
            ...
            $this->buildTeacherResponse(...[])
        );
        $author = (new UserBuilder())
            ->withId(new Uuid($request->getRequestAuthorId()))
            ->build();
        $user = (new UserBuilder())
            ->withId(new Uuid($request->getUserId()))
            ->build();

        $this->userRepository
            ->method('ofId')
            ->withConsecutive([$request->getRequestAuthorId()], [$request->getUserId()])
            ->willReturn($author, $user);

        $this->userConnectionRepository->expects(self::once())->method('matching')->willReturn([]);

        $userConnectionCollectionResponse = ($this->searchUserConnectionService)($request);
        $this->assertEquals($expectedUserConnectionCollectionResponse, $userConnectionCollectionResponse);
    }

    public function testWhenRequestIsValidAndAuthorIsStudentThenReturnUserConnection()
    {
        $request = new SearchUserConnectionsByCriteriaRequest(
            '48d34c63-6bba-4c72-a461-8aac1fd7d138',
            '48d34c63-6bba-4c72-a461-8aac1fd7d138',
            null,
            null,
            null,
            null,
            null
        );
        $author = (new UserBuilder())
            ->withId(new Uuid($request->getRequestAuthorId()))
            ->build();
        $user = (new UserBuilder())
            ->withId(new Uuid($request->getUserId()))
            ->withRoles(Roles::fromArrayOfPrimitives([Role::STUDENT]))
            ->build();
        $otherUser = (new UserBuilder())->build();
        $userConnection = new UserConnection($user->getId(), $otherUser->getId(), new Pended(), $user->getId());
        $expectedUserConnectionCollectionResponse = new UserConnectionCollectionResponse(
            ...
            $this->buildStudentResponse($userConnection)
        );

        $this->userRepository
            ->method('ofId')
            ->withConsecutive([$request->getRequestAuthorId()], [$user->getId()])
            ->willReturn($author, $user);

        $this->userConnectionRepository->expects(self::once())->method('matching')->willReturn([$userConnection]);

        $userConnectionCollectionResponse = ($this->searchUserConnectionService)($request);
        $this->assertEquals($expectedUserConnectionCollectionResponse, $userConnectionCollectionResponse);
    }

    private function buildStudentResponse(UserConnection ...$connections): array
    {
        return array_map(
            static function (UserConnection $connection) {
                return new UserConnectionResponse(
                    $connection->getStudentId()->toString(),
                    $connection->getTeacherId()->toString(),
                    (string)$connection->getState(),
                    $connection->getSpecifierId()->toString()
                );
            },
            $connections
        );
    }

    public function testWhenRequestIsValidAndAuthorIsTeacherThenSearchUserConnection()
    {
        $request = new SearchUserConnectionsByCriteriaRequest(
            '48d34c63-6bba-4c72-a461-8aac1fd7d138',
            '48d34c63-6bba-4c72-a461-8aac1fd7d138',
            null,
            null,
            null,
            null,
            null
        );
        $author = (new UserBuilder())
            ->withId(new Uuid($request->getRequestAuthorId()))
            ->build();
        $user = (new UserBuilder())
            ->withId(new Uuid($request->getUserId()))
            ->withRoles(Roles::fromArrayOfPrimitives([Role::TEACHER]))
            ->build();
        $otherUser = (new UserBuilder())->build();
        $userConnection = new UserConnection($user->getId(), $otherUser->getId(), new Pended(), $user->getId());
        $expectedUserConnectionCollectionResponse = new UserConnectionCollectionResponse(
            ...
            $this->buildTeacherResponse($userConnection)
        );

        $this->userRepository
            ->method('ofId')
            ->withConsecutive([$request->getRequestAuthorId()], [$request->getUserId()])
            ->willReturn($author, $user);

        $this->userConnectionRepository->expects(self::once())->method('matching')->willReturn([$userConnection]);

        $userConnectionCollectionResponse = ($this->searchUserConnectionService)($request);
        $this->assertEquals($expectedUserConnectionCollectionResponse, $userConnectionCollectionResponse);
    }

    private function buildTeacherResponse(UserConnection ...$connections): array
    {
        return array_map(
            static function (UserConnection $connection) {
                return new UserConnectionResponse(
                    $connection->getTeacherId()->toString(),
                    $connection->getStudentId()->toString(),
                    (string)$connection->getState(),
                    $connection->getSpecifierId()->toString()
                );
            },
            $connections
        );
    }

}
