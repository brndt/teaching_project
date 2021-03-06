<?php

declare(strict_types=1);

namespace Test\LaSalle\StudentTeacher\User\RefreshToken\Application;

use DateTimeImmutable;
use LaSalle\StudentTeacher\Shared\Domain\Exception\InvalidUuidException;
use LaSalle\StudentTeacher\Shared\Domain\RandomStringGenerator;
use LaSalle\StudentTeacher\Shared\Domain\ValueObject\Uuid;
use LaSalle\StudentTeacher\User\RefreshToken\Application\Request\GenerateTokensRequest;
use LaSalle\StudentTeacher\User\RefreshToken\Application\Response\TokensResponse;
use LaSalle\StudentTeacher\User\RefreshToken\Application\Service\GenerateTokensService;
use LaSalle\StudentTeacher\User\RefreshToken\Domain\Repository\RefreshTokenRepository;
use LaSalle\StudentTeacher\User\User\Domain\Repository\UserRepository;
use LaSalle\StudentTeacher\User\User\Domain\TokenManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Test\LaSalle\StudentTeacher\User\RefreshToken\Domain\RefreshTokenBuilder;
use Test\LaSalle\StudentTeacher\User\User\Domain\UserBuilder;

final class GenerateTokensServiceTest extends TestCase
{
    private GenerateTokensService $generateTokensService;
    private MockObject $refreshTokenRepository;
    private MockObject $tokenManager;
    private MockObject $userRepository;
    private MockObject $randomStringGenerator;

    public function setUp(): void
    {
        $this->refreshTokenRepository = $this->createMock(RefreshTokenRepository::class);
        $this->tokenManager = $this->createMock(TokenManager::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->randomStringGenerator = $this->createMock(RandomStringGenerator::class);
        $this->generateTokensService = new GenerateTokensService(
            $this->refreshTokenRepository,
            $this->tokenManager,
            $this->userRepository,
            $this->randomStringGenerator
        );
    }

    public function testWhenUserIdIsInvalidThenThrowException()
    {
        $this->expectException(InvalidUuidException::class);

        $request = new GenerateTokensRequest('16bf6c6a-c855-4a36-a3dd-5b9f6d92c753-invalid', new DateTimeImmutable());

        ($this->generateTokensService)($request);
    }

    public function testWhenRequestIsValidThenGenerateTokens()
    {
        $request = new GenerateTokensRequest('cfe849f3-7832-435a-b484-83fabf530794', new DateTimeImmutable());
        $refreshToken = (new RefreshTokenBuilder())
            ->withUserId(new Uuid($request->getUserId()))
            ->withExpirationDate($request->getExpirationDate())
            ->build();
        $user = (new UserBuilder())
            ->withId($refreshToken->getUserId())
            ->build();
        $expectedTokensResponse = new TokensResponse(
            'token_string',
            $refreshToken->getRefreshToken()->toString(),
            $refreshToken->getUserId()->toString()
        );

        $this->randomStringGenerator->method('generate')->willReturn($refreshToken->getRefreshToken()->toString());
        $this->refreshTokenRepository->expects(self::once())->method('save')->with($refreshToken);
        $this->userRepository
            ->expects(self::once())
            ->method('ofId')
            ->with($refreshToken->getUserId())
            ->willReturn($user);
        $this->tokenManager
            ->expects(self::once())
            ->method('generate')
            ->with($user)
            ->willReturn($expectedTokensResponse->getToken());

        $tokensResponse = ($this->generateTokensService)($request);
        $this->assertEquals($expectedTokensResponse, $tokensResponse);
    }
}
