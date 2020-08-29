<?php

declare(strict_types=1);

namespace LaSalle\StudentTeacher\Resource\Domain\Service;

use LaSalle\StudentTeacher\Resource\Application\Exception\CourseNotFoundException;
use LaSalle\StudentTeacher\Resource\Domain\Aggregate\Course;
use LaSalle\StudentTeacher\Resource\Domain\Repository\CourseRepository;
use LaSalle\StudentTeacher\Shared\Application\Exception\PermissionDeniedException;
use LaSalle\StudentTeacher\Shared\Domain\Criteria\Filter;
use LaSalle\StudentTeacher\Shared\Domain\Criteria\Filters;
use LaSalle\StudentTeacher\Shared\Domain\ValueObject\Uuid;
use LaSalle\StudentTeacher\User\Domain\Aggregate\User;
use LaSalle\StudentTeacher\User\Domain\ValueObject\Role;

final class CourseService
{
    private CourseRepository $repository;

    public function __construct(CourseRepository $repository)
    {
        $this->repository = $repository;
    }

    public function findCourse(Uuid $id): Course
    {
        $course = $this->repository->ofId($id);
        if (null === $course) {
            throw new CourseNotFoundException();
        }
        return $course;
    }

    public function createFiltersDependingByRoles(User $user): Filters
    {
        {
            if (true === $user->isInRole(new Role(Role::ADMIN))) {
                return Filters::fromValues([]);
            }
            if (true === $user->isInRole(new Role(Role::TEACHER))) {
                return Filters::fromValues(
                    [['field' => 'teacherId', 'operator' => '=', 'value' => $user->getId()->toString()]]
                );
            }
            throw new PermissionDeniedException();
        }
    }

    public function createFilterByTeacherId(Uuid $userId): Filter
    {
        return Filter::fromValues(['field' => 'teacherId', 'operator' => '=', 'value' => $userId->toString()]);
    }
}
