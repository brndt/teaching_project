<?php

declare(strict_types=1);

namespace LaSalle\StudentTeacher\User\RefreshToken\Application\Service;

use InvalidArgumentException;
use LaSalle\StudentTeacher\Shared\Domain\Exception\InvalidUuidException;
use LaSalle\StudentTeacher\Shared\Domain\RandomStringGenerator;
use LaSalle\StudentTeacher\Shared\Domain\ValueObject\Uuid;
use LaSalle\StudentTeacher\User\RefreshToken\Application\Exception\RefreshTokenIsExpiredException;
use LaSalle\StudentTeacher\User\RefreshToken\Application\Exception\RefreshTokenNotFoundException;
use LaSalle\StudentTeacher\User\RefreshToken\Domain\Aggregate\RefreshToken;
use LaSalle\StudentTeacher\User\RefreshToken\Domain\Repository\RefreshTokenRepository;
use LaSalle\StudentTeacher\User\User\Domain\Repository\UserRepository;
use LaSalle\StudentTeacher\User\User\Domain\TokenManager;
use LaSalle\StudentTeacher\User\User\Domain\ValueObject\Token;

abstract class RefreshTokenService
{
    public function __construct(
        protected RefreshTokenRepository $refreshTokenRepository,
        protected TokenManager $tokenManager,
        protected UserRepository $userRepository,
        protected RandomStringGenerator $randomStringGenerator
    ) {
    }

    protected function createIdFromPrimitive(string $uuid): Uuid
    {
        try {
            return new Uuid($uuid);
        } catch (InvalidUuidException $error) {
            throw new InvalidArgumentException($error->getMessage());
        }
    }

    protected function ensureRefreshTokenExists(?RefreshToken $refreshToken): void
    {
        if (null === $refreshToken) {
            throw new RefreshTokenNotFoundException();
        }
    }

    protected function ensureRefreshTokenIsNotExpired(RefreshToken $refreshToken): void
    {
        if (true === $refreshToken->isExpired()) {
            throw new RefreshTokenIsExpiredException();
        }
    }

    protected function generateToken(RefreshToken $refreshToken): string
    {
        $user = $this->userRepository->ofId($refreshToken->getUserId());
        return $this->tokenManager->generate($user);
    }

    protected function searchRefreshToken(Token $token): RefreshToken
    {
        $refreshToken = $this->refreshTokenRepository->ofToken($token);
        if (null === $refreshToken) {
            throw new RefreshTokenNotFoundException();
        }
        return $refreshToken;
    }
}