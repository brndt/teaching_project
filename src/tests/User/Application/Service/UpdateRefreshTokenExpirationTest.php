<?php

declare(strict_types=1);

namespace Test\LaSalle\StudentTeacher\User\Application\Service;

use LaSalle\StudentTeacher\Shared\Domain\ValueObject\Uuid;
use LaSalle\StudentTeacher\User\Application\Exception\RefreshTokenIsExpiredException;
use LaSalle\StudentTeacher\User\Application\Exception\RefreshTokenNotFoundException;
use LaSalle\StudentTeacher\User\Application\Request\UpdateRefreshTokenExpirationRequest;
use LaSalle\StudentTeacher\User\Application\Service\UpdateRefreshTokenExpirationService;
use LaSalle\StudentTeacher\User\Domain\Aggregate\RefreshToken;
use LaSalle\StudentTeacher\User\Domain\Repository\RefreshTokenRepository;
use LaSalle\StudentTeacher\User\Domain\Repository\UserRepository;
use LaSalle\StudentTeacher\User\Domain\TokenManager;
use LaSalle\StudentTeacher\User\Domain\ValueObject\Token;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class UpdateRefreshTokenExpirationTest extends TestCase
{
    private MockObject $repository;
    private MockObject $tokenManager;
    private MockObject $userRepository;
    private UpdateRefreshTokenExpirationService $updateRefreshTokenValidation;

    public function setUp(): void
    {
        $this->repository = $this->createMock(RefreshTokenRepository::class);
        $this->tokenManager = $this->createMock(TokenManager::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->updateRefreshTokenValidation = new UpdateRefreshTokenExpirationService($this->repository, $this->tokenManager, $this->userRepository);
    }

    public function testWhenRefreshTokenNotFoundThenThrowException()
    {
        $this->expectException(RefreshTokenNotFoundException::class);
        $this->repository->method('ofToken')->willReturn(null);
        ($this->updateRefreshTokenValidation)($this->anyValidRefreshTokenRequest());
    }

    public function testWhenRefreshTokenIsExpiredThenThrowException()
    {
        $this->expectException(RefreshTokenIsExpiredException::class);
        $this->repository->method('ofToken')->willReturn($this->anyExpiredRefreshToken());
        ($this->updateRefreshTokenValidation)($this->anyValidRefreshTokenRequest());
    }

    private function anyExpiredRefreshToken(): RefreshToken
    {
        return new RefreshToken(Token::generate(), Uuid::generate(), new \DateTime());
    }

    private function anyValidRefreshTokenRequest(): UpdateRefreshTokenExpirationRequest
    {
        return new UpdateRefreshTokenExpirationRequest(
            Token::generate()->toString(),
            new \DateTime('+1 day')
        );
    }

}