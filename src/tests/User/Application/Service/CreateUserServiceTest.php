<?php

declare(strict_types=1);

namespace Test\LaSalle\StudentTeacher\User\Application\Service;

use InvalidArgumentException;
use LaSalle\StudentTeacher\Shared\Application\Exception\PermissionDeniedException;
use LaSalle\StudentTeacher\Shared\Domain\Event\DomainEventBus;
use LaSalle\StudentTeacher\Shared\Domain\ValueObject\Uuid;
use LaSalle\StudentTeacher\User\Application\Exception\UserAlreadyExistsException;
use LaSalle\StudentTeacher\User\Application\Request\CreateUserRequest;
use LaSalle\StudentTeacher\User\Application\Service\CreateUserService;
use LaSalle\StudentTeacher\User\Domain\Aggregate\User;
use LaSalle\StudentTeacher\User\Domain\Repository\UserRepository;
use LaSalle\StudentTeacher\User\Domain\ValueObject\Email;
use LaSalle\StudentTeacher\User\Domain\ValueObject\Name;
use LaSalle\StudentTeacher\User\Domain\ValueObject\Password;
use LaSalle\StudentTeacher\User\Domain\ValueObject\Roles;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CreateUserServiceTest extends TestCase
{
    private CreateUserService $createUser;
    private MockObject $repository;
    private MockObject $eventBus;

    public function setUp(): void
    {
        $this->repository = $this->createMock(UserRepository::class);
        $this->eventBus = $this->createMock(DomainEventBus::class);
        $this->createUser = new CreateUserService($this->repository, $this->eventBus);
    }

    public function testWhenUserEmailIsInvalidThenThrowException()
    {
        $this->expectException(InvalidArgumentException::class);
        ($this->createUser)($this->anyUserRequestWithInvalidEmail());
    }

    public function testWhenUserAlreadyExistsThenThrowException()
    {
        $this->repository->method('ofEmail')->willReturn($this->anyValidUser());
        $this->expectException(UserAlreadyExistsException::class);
        ($this->createUser)($this->anyValidUserRequest());
    }

    public function testWhenUserPasswordIsInvalidThenThrowException()
    {
        $this->expectException(InvalidArgumentException::class);
        ($this->createUser)($this->anyUserRequestWithInvalidPasswordLength());
    }

    public function testWhenPasswordDoesntContainNumberThenThrowException()
    {
        $this->expectException(InvalidArgumentException::class);
        ($this->createUser)($this->anyUserRequestWithInvalidNumberContaining());
    }

    public function testWhenPasswordDoesntContainLetterThenThrowException()
    {
        $this->expectException(InvalidArgumentException::class);
        ($this->createUser)($this->anyUserRequestWithInvalidLetterContaining());
    }

    public function testWhenFirstNameIsInvalidThenThrowException()
    {
        $this->expectException(InvalidArgumentException::class);
        ($this->createUser)($this->anyUserRequestWithInvalidFirstName());
    }

    public function testWhenLastNameIsInvalidThenThrowException()
    {
        $this->expectException(InvalidArgumentException::class);
        ($this->createUser)($this->anyUserRequestWithInvalidLastName());
    }

    public function testWhenUserRoleIsInvalidThenThrowException()
    {
        $this->expectException(InvalidArgumentException::class);
        ($this->createUser)($this->anyUserRequestWithInvalidRole());
    }

    public function testWhenUserRoleIsAdminThenThrowException()
    {
        $this->expectException(PermissionDeniedException::class);
        ($this->createUser)($this->anyUserRequestWithAdminRole());
    }

    public function testWhenRequestIsValidThenCreateUser()
    {
        $this->repository->expects($this->once())->method('ofEmail')->willReturn(null);
        $this->repository->expects($this->once())->method('save')->with($this->callback($this->userComparator($this->anyValidUser())));
        $this->eventBus->expects($this->atLeastOnce())->method('dispatch');
        ($this->createUser)($this->anyValidUserRequest());
    }

    private function anyValidUser(): User
    {
        return new User(
            new Uuid('16bf6c6a-c855-4a36-a3dd-5b9f6d92c753'),
            new Email('user@example.com'),
            Password::fromPlainPassword('123456aa'),
            new Name('Alex'),
            new Name('Johnson'),
            Roles::fromArrayOfPrimitives(['teacher']),
            new \DateTimeImmutable('2020-04-27'),
            false
        );
    }

    private function anyValidUserRequest(): CreateUserRequest
    {
        return new CreateUserRequest(
            'user@example.com',
            '123456aa',
            'Alex',
            'Johnson',
            ['teacher'],
            new \DateTimeImmutable('2020-04-27')
        );
    }

    private function anyUserRequestWithInvalidEmail(): CreateUserRequest
    {
        return new CreateUserRequest(
            'userexample.com',
            '123456aa',
            'Alex',
            'Johnson',
            ['teacher'],
            new \DateTimeImmutable('2020-04-27')
        );
    }

    private function anyUserRequestWithInvalidRole(): CreateUserRequest
    {
        return new CreateUserRequest(
            'user@example.com',
            '123456aa',
            'Alex',
            'Johnson',
            ['something_invalid'],
            new \DateTimeImmutable('2020-04-27')
        );
    }

    private function anyUserRequestWithAdminRole(): CreateUserRequest
    {
        return new CreateUserRequest(
            'user@example.com',
            '123456aa',
            'Alex',
            'Johnson',
            ['admin'],
            new \DateTimeImmutable('2020-04-27')
        );
    }

    private function anyUserRequestWithInvalidPasswordLength(): CreateUserRequest
    {
        return new CreateUserRequest(
            'user@example.com',
            '123456a',
            'Alex',
            'Johnson',
            ['teacher'],
            new \DateTimeImmutable('2020-04-27')
        );
    }

    private function anyUserRequestWithInvalidNumberContaining(): CreateUserRequest
    {
        return new CreateUserRequest(
            'user@example.com',
            'qwertyuiop',
            'Alex',
            'Johnson',
            ['teacher'],
            new \DateTimeImmutable('2020-04-27')
        );
    }

    private function anyUserRequestWithInvalidLetterContaining(): CreateUserRequest
    {
        return new CreateUserRequest(
            'user@example.com',
            '123456789',
            'Alex',
            'Johnson',
            ['teacher'],
            new \DateTimeImmutable()
        );
    }

    private function anyUserRequestWithInvalidFirstName(): CreateUserRequest
    {
        return new CreateUserRequest(
            'user@example.com',
            '123456aa',
            'Alex.',
            'Johnson',
            ['teacher'],
            new \DateTimeImmutable()
        );
    }

    private function anyUserRequestWithInvalidLastName(): CreateUserRequest
    {
        return new CreateUserRequest(
            'user@example.com',
            '123456aa',
            'Alex',
            'Johnson ',
            ['teacher'],
            new \DateTimeImmutable()
        );
    }

    private function userComparator(User $userExpected): callable
    {
        return function (User $userActual) use ($userExpected) {
            return $userExpected->getEmail()->toString() === $userActual->getEmail()->toString()
                && $userExpected->getFirstName()->toString() === $userActual->getFirstName()->toString()
                && $userExpected->getLastName()->toString() === $userActual->getLastName()->toString()
                && $userExpected->getRoles()->getArrayOfPrimitives() === $userActual->getRoles()->getArrayOfPrimitives()
                && $userExpected->getCreated() == $userActual->getCreated();
        };
    }
}