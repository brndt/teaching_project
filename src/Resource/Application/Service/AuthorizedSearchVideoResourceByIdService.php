<?php

declare(strict_types=1);

namespace LaSalle\StudentTeacher\Resource\Application\Service;

use LaSalle\StudentTeacher\Resource\Application\Request\AuthorizedSearchVideoResourceByIdRequest;
use LaSalle\StudentTeacher\Resource\Application\Response\VideoResourceResponse;
use LaSalle\StudentTeacher\Resource\Domain\Aggregate\VideoResource;
use LaSalle\StudentTeacher\Resource\Domain\Repository\CourseRepository;
use LaSalle\StudentTeacher\Resource\Domain\Repository\ResourceRepository;
use LaSalle\StudentTeacher\Resource\Domain\Repository\UnitRepository;
use LaSalle\StudentTeacher\Resource\Domain\Service\CourseService;
use LaSalle\StudentTeacher\Resource\Domain\Service\ResourceService;
use LaSalle\StudentTeacher\Resource\Domain\Service\UnitService;
use LaSalle\StudentTeacher\Shared\Domain\ValueObject\Uuid;
use LaSalle\StudentTeacher\User\Domain\Repository\UserRepository;
use LaSalle\StudentTeacher\User\Domain\Service\AuthorizationService;
use LaSalle\StudentTeacher\User\Domain\Service\UserService;

final class AuthorizedSearchVideoResourceByIdService
{
    private CourseService $courseService;
    private UserService $userService;
    private ResourceService $resourceService;
    private UnitService $unitService;

    public function __construct(
        CourseRepository $courseRepository,
        UserRepository $userRepository,
        UnitRepository $unitRepository,
        ResourceRepository $resourceRepository,
        private AuthorizationService $authorizationService
    ) {
        $this->resourceService = new ResourceService($resourceRepository);
        $this->unitService = new UnitService($unitRepository);
        $this->courseService = new CourseService($courseRepository);
        $this->userService = new UserService($userRepository);
    }

    public function __invoke(AuthorizedSearchVideoResourceByIdRequest $request): VideoResourceResponse
    {
        $authorId = new Uuid($request->getRequestAuthorId());
        $requestAuthor = $this->userService->findUser($authorId);

        $resourceId = new Uuid($request->getResourceId());
        $resource = $this->resourceService->findResource($resourceId);

        $unit = $this->unitService->findUnit($resource->getUnitId());
        $course = $this->courseService->findCourse($unit->getCourseId());

        $this->authorizationService->ensureUserHasPermissionsToManageCourse($requestAuthor, $course);

        return $this->buildResponse($resource);
    }

    private function buildResponse(VideoResource $resource): VideoResourceResponse
    {
        return new VideoResourceResponse(
            $resource->getId()->toString(),
            $resource->getUnitId()->toString(),
            $resource->getName(),
            $resource->getDescription(),
            $resource->getCreated(),
            $resource->getModified(),
            $resource->getStatus()->value(),
            $resource->getContent(),
            $resource->getVideoURL(),
            $resource->getVideoDescription()
        );
    }

}
