<?php

declare(strict_types=1);

namespace LaSalle\StudentTeacher\User\RefreshToken\Infrastructure\Framework\Controller;

use DateTimeImmutable;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamFetcher;
use LaSalle\StudentTeacher\User\RefreshToken\Application\Request\UpdateRefreshTokenExpirationRequest;
use LaSalle\StudentTeacher\User\RefreshToken\Application\Service\UpdateRefreshTokenExpirationService;
use LaSalle\StudentTeacher\User\Shared\Application\Request\SearchUserCredentialsByIdRequest;
use LaSalle\StudentTeacher\User\Shared\Application\Request\SearchUsersByCriteriaRequest;
use LaSalle\StudentTeacher\User\Shared\Application\Service\SearchUserCredentialsByIdService;
use LaSalle\StudentTeacher\User\Shared\Application\Service\SearchUsersByCriteriaService;
use Symfony\Component\HttpFoundation\Response;

final class UpdateTokensController extends AbstractFOSRestController
{
    public function __construct(
        private UpdateRefreshTokenExpirationService $updateRefreshTokenExpiration,
        private SearchUsersByCriteriaService $searchUsersByCriteriaService,
        private SearchUserCredentialsByIdService $searchUserCredentialsByIdService
    ) {
    }

    /**
     * @Rest\Post("/api/v1/users/token_refresh")
     * @RequestParam(name="refresh_token")
     */
    public function postAction(ParamFetcher $paramFetcher): Response
    {
        $refreshToken = $paramFetcher->get('refresh_token');

        $refreshTokensResponse = ($this->updateRefreshTokenExpiration)(
            new UpdateRefreshTokenExpirationRequest($refreshToken, new DateTimeImmutable('+ 2592000 seconds'))
        );

        $filters = [['field' => 'id', 'operator' => '=', 'value' => $refreshTokensResponse->getUserId()]];
        $userResponse = ($this->searchUsersByCriteriaService)(
            new SearchUsersByCriteriaRequest($filters, null, null, null, null, null)
        )->getIterator()->current();

        $email = ($this->searchUserCredentialsByIdService)(
            new SearchUserCredentialsByIdRequest($refreshTokensResponse->getUserId())
        )->getEmail();

        $response = array_merge(
            $userResponse->toPrimitives(),
            [
                'email' => $email,
                'token' => $refreshTokensResponse->getToken(),
                'refreshToken' => $refreshTokensResponse->getRefreshToken()
            ]
        );

        return $this->handleView($this->view($response, Response::HTTP_OK));
    }
}
