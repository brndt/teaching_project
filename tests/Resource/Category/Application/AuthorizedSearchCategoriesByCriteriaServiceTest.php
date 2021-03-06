<?php

declare(strict_types=1);

namespace Test\LaSalle\StudentTeacher\Resource\Category\Application;

use LaSalle\StudentTeacher\Resource\Category\Application\Request\AuthorizedSearchCategoriesByCriteriaRequest;
use LaSalle\StudentTeacher\Resource\Category\Application\Response\CategoryCollectionResponse;
use LaSalle\StudentTeacher\Resource\Category\Application\Response\CategoryResponse;
use LaSalle\StudentTeacher\Resource\Category\Application\Service\AuthorizedSearchCategoriesByCriteriaService;
use LaSalle\StudentTeacher\Resource\Category\Domain\Aggregate\Category;
use LaSalle\StudentTeacher\Resource\Category\Domain\Repository\CategoryRepository;
use LaSalle\StudentTeacher\Resource\Course\Domain\Repository\CourseRepository;
use LaSalle\StudentTeacher\Resource\CoursePermission\Domain\Repository\CoursePermissionRepository;
use LaSalle\StudentTeacher\Resource\Unit\Domain\Repository\UnitRepository;
use LaSalle\StudentTeacher\Shared\Application\Exception\PermissionDeniedException;
use LaSalle\StudentTeacher\Shared\Domain\Exception\InvalidUuidException;
use LaSalle\StudentTeacher\Shared\Domain\ValueObject\Uuid;
use LaSalle\StudentTeacher\User\Shared\Application\Exception\UserNotFoundException;
use LaSalle\StudentTeacher\User\User\Domain\Repository\UserRepository;
use LaSalle\StudentTeacher\User\User\Domain\Service\AuthorizationService;
use LaSalle\StudentTeacher\User\User\Domain\ValueObject\Role;
use LaSalle\StudentTeacher\User\User\Domain\ValueObject\Roles;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Test\LaSalle\StudentTeacher\Resource\Category\Domain\CategoryBuilder;
use Test\LaSalle\StudentTeacher\User\User\Domain\UserBuilder;

final class AuthorizedSearchCategoriesByCriteriaServiceTest extends TestCase
{
    private AuthorizedSearchCategoriesByCriteriaService $searchCategoriesByCriteria;
    private MockObject $categoryRepository;
    private MockObject $userRepository;

    public function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $coursePermissionRepository = $this->createMock(CoursePermissionRepository::class);
        $unitRepository = $this->createMock(UnitRepository::class);
        $courseRepository = $this->createMock(CourseRepository::class);
        $authorizationService = new AuthorizationService($coursePermissionRepository, $unitRepository, $courseRepository);
        $this->searchCategoriesByCriteria = new AuthorizedSearchCategoriesByCriteriaService(
            $this->userRepository, $this->categoryRepository,
            $authorizationService
        );
    }

    public function testWhenRequestAuthorIsInvalidThenThrowException()
    {
        $request = new AuthorizedSearchCategoriesByCriteriaRequest(
            '48d34c63-6bba-4c72-a461-8aac1fd7d138-invalid',
            [],
            null,
            null,
            null,
            null,
            null
        );

        $this->expectException(InvalidUuidException::class);
        ($this->searchCategoriesByCriteria)($request);
    }

    public function testWhenUserIdIsNotFoundThenThrowException()
    {
        $request = new AuthorizedSearchCategoriesByCriteriaRequest(
            '48d34c63-6bba-4c72-a461-8aac1fd7d138',
            [],
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
            ->with(new Uuid($request->getRequestAuthorId()))
            ->willReturn(null);
        ($this->searchCategoriesByCriteria)($request);
    }

    public function testWhenRequestAuthorIsNotAdminThenThrowException()
    {
        $request = new AuthorizedSearchCategoriesByCriteriaRequest(
            '48d34c63-6bba-4c72-a461-8aac1fd7d138',
            [],
            null,
            null,
            null,
            null,
            null
        );
        $user = (new UserBuilder())
            ->withRoles(Roles::fromArrayOfPrimitives([Role::STUDENT]))
            ->build();
        $this->expectException(PermissionDeniedException::class);
        $this->userRepository
            ->expects(self::once())
            ->method('ofId')
            ->with(new Uuid($request->getRequestAuthorId()))
            ->willReturn($user);
        ($this->searchCategoriesByCriteria)($request);
    }

    public function testWhenCategoriesDontExistThenReturnEmptyArray()
    {
        $request = new AuthorizedSearchCategoriesByCriteriaRequest(
            '48d34c63-6bba-4c72-a461-8aac1fd7d138',
            [],
            null,
            null,
            null,
            null,
            null
        );

        $user = (new UserBuilder())
            ->withRoles(Roles::fromArrayOfPrimitives([Role::ADMIN]))
            ->build();
        $expectedCategoryCollectionResponse = new CategoryCollectionResponse(
            ...
            $this->buildResponse(...[])
        );
        $this->userRepository
            ->expects(self::once())
            ->method('ofId')
            ->with(new Uuid($request->getRequestAuthorId()))
            ->willReturn($user);
        $this->categoryRepository
            ->expects(self::once())
            ->method('matching')
            ->willReturn([]);
        $actualCategoryCollectionResponse = ($this->searchCategoriesByCriteria)($request);
        $this->assertEquals($expectedCategoryCollectionResponse, $actualCategoryCollectionResponse);
    }

    public function testWhenRequestIsValidThenReturnCategories()
    {
        $request = new AuthorizedSearchCategoriesByCriteriaRequest(
            '48d34c63-6bba-4c72-a461-8aac1fd7d138',
            [],
            null,
            null,
            null,
            null,
            null
        );
        $category = (new CategoryBuilder())->build();
        $anotherCategory = (new CategoryBuilder())->build();
        $user = (new UserBuilder())
            ->withRoles(Roles::fromArrayOfPrimitives([Role::ADMIN]))
            ->build();
        $expectedCategoryCollectionResponse = new CategoryCollectionResponse(
            ...
            $this->buildResponse(...[$category, $anotherCategory])
        );
        $this->userRepository
            ->expects(self::once())
            ->method('ofId')
            ->with(new Uuid($request->getRequestAuthorId()))
            ->willReturn($user);
        $this->categoryRepository
            ->expects(self::once())
            ->method('matching')
            ->willReturn([$category, $anotherCategory]);

        $actualCategoryCollectionResponse = ($this->searchCategoriesByCriteria)($request);
        $this->assertEquals($expectedCategoryCollectionResponse, $actualCategoryCollectionResponse);
    }

    private function buildResponse(Category ...$categories): array
    {
        return array_map(
            static function (Category $category) {
                return new CategoryResponse(
                    $category->getId()->toString(),
                    $category->getName(),
                    $category->getStatus()->value(),
                );
            },
            $categories
        );
    }

}
