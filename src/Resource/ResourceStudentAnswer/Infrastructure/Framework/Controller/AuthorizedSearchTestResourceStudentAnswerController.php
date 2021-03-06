<?php

declare(strict_types=1);

namespace LaSalle\StudentTeacher\Resource\ResourceStudentAnswer\Infrastructure\Framework\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Request\ParamFetcher;
use LaSalle\StudentTeacher\Resource\ResourceStudentAnswer\Application\Request\AuthorizedSearchTestResourceStudentAnswerRequest;
use LaSalle\StudentTeacher\Resource\ResourceStudentAnswer\Application\Service\AuthorizedSearchTestResourceStudentAnswerService;
use Symfony\Component\HttpFoundation\Response;

final class AuthorizedSearchTestResourceStudentAnswerController extends AbstractFOSRestController
{
    public function __construct(
        private AuthorizedSearchTestResourceStudentAnswerService $searchTestResourceStudentAnswerService
    ) {
    }

    /**
     * @Rest\Get("/api/v1/panel/test_resource_student_permission")
     * @QueryParam(name="resourceId", nullable=false)
     * @QueryParam(name="studentId", nullable=false)
     */
    public function __invoke(ParamFetcher $paramFetcher): Response
    {
        $requestAuthorId = $this->getUser()->getId();
        $resourceId = $paramFetcher->get('resourceId');
        $studentId = $paramFetcher->get('studentId');

        $studentAnswerResponse = ($this->searchTestResourceStudentAnswerService)(
            new AuthorizedSearchTestResourceStudentAnswerRequest(
                $requestAuthorId,
                $resourceId,
                $studentId
            )
        );

        return $this->handleView($this->view($studentAnswerResponse, Response::HTTP_OK));
    }
}
