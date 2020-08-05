<?php

declare(strict_types=1);

namespace Test\LaSalle\StudentTeacher\Resource\Application;

use InvalidArgumentException;
use LaSalle\StudentTeacher\Resource\Application\Exception\CourseNotFoundException;
use LaSalle\StudentTeacher\Resource\Application\Request\AuthorizedSearchCourseByIdRequest;
use LaSalle\StudentTeacher\Resource\Application\Request\SearchCourseRequest;
use LaSalle\StudentTeacher\Resource\Application\Response\CourseResponse;
use LaSalle\StudentTeacher\Resource\Application\Service\AuthorizedSearchCourseByIdService;
use LaSalle\StudentTeacher\Resource\Application\Service\SearchCourseService;
use LaSalle\StudentTeacher\Resource\Domain\Aggregate\Course;
use LaSalle\StudentTeacher\Resource\Domain\Repository\CategoryRepository;
use LaSalle\StudentTeacher\Resource\Domain\Repository\CourseRepository;
use LaSalle\StudentTeacher\Shared\Domain\ValueObject\Uuid;
use LaSalle\StudentTeacher\User\Application\Exception\UserNotFoundException;
use LaSalle\StudentTeacher\User\Domain\Repository\UserRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Test\LaSalle\StudentTeacher\Resource\Builder\CourseBuilder;
use Test\LaSalle\StudentTeacher\User\Builder\UserBuilder;

final class AuthorizedSearchCourseByIdServiceTest extends TestCase
{
    private AuthorizedSearchCourseByIdService $searchCourseService;
    private MockObject $courseRepository;
    private MockObject $categoryRepository;
    private MockObject $userRepository;

    public function setUp(): void
    {
        $this->courseRepository = $this->createMock(CourseRepository::class);
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->searchCourseService = new AuthorizedSearchCourseByIdService(
            $this->courseRepository,
            $this->categoryRepository,
            $this->userRepository
        );
    }

    public function testWhenRequestAuthorIsInvalidThenThrowException()
    {
        $request = new AuthorizedSearchCourseByIdRequest(
            Uuid::generate()->toString() . '-invalid',
            Uuid::generate()->toString(),
        );

        $this->expectException(InvalidArgumentException::class);
        ($this->searchCourseService)($request);
    }

    public function testWhenRequestAuthorIsNotFoundThenThrowException()
    {
        $request = new AuthorizedSearchCourseByIdRequest(
            Uuid::generate()->toString(),
            Uuid::generate()->toString(),
        );

        $this->expectException(UserNotFoundException::class);
        $this->userRepository
            ->expects($this->once())
            ->method('ofId')
            ->with($request->getRequestAuthorId())
            ->willReturn(null);
        ($this->searchCourseService)($request);
    }

    public function testWhenCourseIdIsInvalidThenThrowException()
    {
        $request = new AuthorizedSearchCourseByIdRequest(
            Uuid::generate()->toString(),
            Uuid::generate()->toString() . '-invalid',
        );

        $author = (new UserBuilder())
            ->withId(new Uuid($request->getRequestAuthorId()))
            ->build();

        $this->expectException(InvalidArgumentException::class);
        $this->userRepository
            ->expects($this->at(0))
            ->method('ofId')
            ->with($request->getRequestAuthorId())
            ->willReturn($author);
        ($this->searchCourseService)($request);
    }

    public function testWhenCourseIsNotFoundThenThrowException()
    {
        $request = new AuthorizedSearchCourseByIdRequest(
            Uuid::generate()->toString(),
            Uuid::generate()->toString(),
        );

        $author = (new UserBuilder())
            ->withId(new Uuid($request->getRequestAuthorId()))
            ->build();

        $this->expectException(CourseNotFoundException::class);
        $this->userRepository
            ->expects($this->once())
            ->method('ofId')
            ->with($request->getRequestAuthorId())
            ->willReturn($author);
        $this->courseRepository
            ->expects($this->once())
            ->method('ofId')
            ->with(new Uuid($request->getCourseId()))
            ->willReturn(null);
        ($this->searchCourseService)($request);
    }

    public function testWhenRequestIsValidThenSearchCourse()
    {
        $request = new AuthorizedSearchCourseByIdRequest(
            Uuid::generate()->toString(),
            Uuid::generate()->toString(),
        );

        $author = (new UserBuilder())
            ->withId(new Uuid($request->getRequestAuthorId()))
            ->build();

        $course = (new CourseBuilder())
            ->withId(new Uuid($request->getCourseId()))
            ->build();
        $expectedCourseResponse = $this->buildResponse($course);

        $this->userRepository
            ->expects($this->once())
            ->method('ofId')
            ->with($request->getRequestAuthorId())
            ->willReturn($author);
        $this->courseRepository
            ->expects($this->once())
            ->method('ofId')
            ->with($course->getId())
            ->willReturn($course);
        $actualCourseResponse = ($this->searchCourseService)($request);
        $this->assertEquals($expectedCourseResponse, $actualCourseResponse);
    }

    private function buildResponse(Course $course)
    {
        return new CourseResponse(
            $course->getId()->toString(),
            $course->getTeacherId()->toString(),
            $course->getCategoryId()->toString(),
            $course->getName(),
            $course->getDescription(),
            $course->getLevel(),
            $course->getCreated(),
            $course->getModified(),
            $course->getStatus()->value(),
        );
    }
}
