<?php

declare(strict_types=1);

namespace LaSalle\StudentTeacher\Resource\ResourceStudentAnswer\Application\Service;

use DateTimeImmutable;
use LaSalle\StudentTeacher\Resource\Resource\Domain\Aggregate\Resource;
use LaSalle\StudentTeacher\Resource\Resource\Domain\Repository\ResourceRepository;
use LaSalle\StudentTeacher\Resource\Resource\Domain\Service\ResourceService;
use LaSalle\StudentTeacher\Resource\ResourceStudentAnswer\Application\Request\CreateTestResourceStudentAnswerRequest;
use LaSalle\StudentTeacher\Resource\ResourceStudentAnswer\Domain\Aggregate\TestResourceStudentAnswer;
use LaSalle\StudentTeacher\Resource\ResourceStudentAnswer\Domain\Repository\ResourceStudentAnswerRepository;
use LaSalle\StudentTeacher\Resource\ResourceStudentAnswer\Domain\Service\ResourceStudentAnswerService;
use LaSalle\StudentTeacher\Resource\ResourceStudentAnswer\Domain\ValueObject\StudentTestAnswer;
use LaSalle\StudentTeacher\Resource\ResourceStudentAnswer\Domain\ValueObject\TestAnswer;
use LaSalle\StudentTeacher\Resource\ResourceStudentAnswer\Domain\ValueObject\TestQuestion;
use LaSalle\StudentTeacher\Shared\Domain\ValueObject\Status;
use LaSalle\StudentTeacher\Shared\Domain\ValueObject\Uuid;
use LaSalle\StudentTeacher\User\User\Domain\Repository\UserRepository;
use LaSalle\StudentTeacher\User\User\Domain\Service\AuthorizationService;
use LaSalle\StudentTeacher\User\User\Domain\Service\UserService;

final class CreateTestResourceStudentAnswerService
{
    private UserService $userService;
    private ResourceService $resourceService;
    private ResourceStudentAnswerService $resourceStudentAnswerService;

    public function __construct(
        UserRepository $userRepository,
        private AuthorizationService $authorizationService,
        ResourceRepository $resourceRepository,
        private ResourceStudentAnswerRepository $repository
    ) {
        $this->userService = new UserService($userRepository);
        $this->resourceService = new ResourceService($resourceRepository);
        $this->resourceStudentAnswerService = new ResourceStudentAnswerService($repository);
    }

    public function __invoke(CreateTestResourceStudentAnswerRequest $request)
    {
        $requestAuthorId = new Uuid($request->getRequestAuthorId());
        $requestAuthor = $this->userService->findUser($requestAuthorId);

        $resourceId = new Uuid($request->getResourceId());
        $resource = $this->resourceService->findResource($resourceId);

        $this->resourceStudentAnswerService->ensureStudentAnswerNotExists($requestAuthorId, $resourceId);

        $this->authorizationService->ensureUserHasAccessToResource($requestAuthor, $resource);

        $assumptions = $this->assumptionMaker($resource, $request->getAssumptions());

        $testResourceStudentAnswer = new TestResourceStudentAnswer(
            $this->repository->nextIdentity(),
            $resourceId,
            $requestAuthorId,
            null,
            null,
            new DateTimeImmutable(),
            null,
            null,
            new Status(Status::PUBLISHED),
            ...$assumptions,
        );

        $this->repository->save($testResourceStudentAnswer);
    }

    private function assumptionMaker(Resource $resource, array $assumptions)
    {
        return array_filter(
            array_map($this->studentTestAnswerMaker($resource), $assumptions),
            fn($value) => !is_null($value)
        );
    }

    private function studentTestAnswerMaker(Resource $resource)
    {
        return static function (array $values) use ($resource): ?StudentTestAnswer {
            $indexOfQuestion = array_search(
                $values['question'],
                array_map(
                    fn(TestQuestion $testQuestion) => $testQuestion->question(),
                    $resource->getQuestions()
                )
            );
            if (false === $indexOfQuestion) {
                return null;
            }
            $indexOfAnswer = array_search(
                $values['student_assumption'],
                array_map(
                    fn(TestAnswer $element) => $element->answer(),
                    $resource->getQuestions()[$indexOfQuestion]->answers()
                )
            );
            if (false === $indexOfAnswer) {
                return null;
            }
            $values['answers'] = array_map(
                fn(TestAnswer $testQuestion) => $testQuestion->toValues(),
                $resource->getQuestions()[$indexOfQuestion]->answers()
            );
            return StudentTestAnswer::fromValues($values);
        };
    }
}
