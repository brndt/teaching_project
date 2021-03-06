<?php

declare(strict_types=1);

namespace LaSalle\StudentTeacher\User\User\Domain\Aggregate;

use DateTime;
use DateTimeImmutable;
use LaSalle\StudentTeacher\Shared\Domain\Event\DomainEvent;
use LaSalle\StudentTeacher\Shared\Domain\ValueObject\Uuid;
use LaSalle\StudentTeacher\User\User\Application\Exception\ConfirmationTokenIsExpiredException;
use LaSalle\StudentTeacher\User\User\Application\Exception\ConfirmationTokenNotFoundException;
use LaSalle\StudentTeacher\User\RefreshToken\Application\Exception\IncorrectConfirmationTokenException;
use LaSalle\StudentTeacher\User\Shared\Application\Exception\UserNotEnabledException;
use LaSalle\StudentTeacher\User\Shared\Application\Exception\UsersAreEqualException;
use LaSalle\StudentTeacher\User\User\Domain\Event\UserCreatedDomainEvent;
use LaSalle\StudentTeacher\User\User\Domain\ValueObject\Email;
use LaSalle\StudentTeacher\User\User\Domain\ValueObject\Name;
use LaSalle\StudentTeacher\User\User\Domain\ValueObject\Password;
use LaSalle\StudentTeacher\User\User\Domain\ValueObject\Role;
use LaSalle\StudentTeacher\User\User\Domain\ValueObject\Roles;
use LaSalle\StudentTeacher\User\User\Domain\ValueObject\Token;

final class User
{
    private array $eventStream;

    public function __construct(
        private Uuid $id,
        private Email $email,
        private Password $password,
        private Name $firstName,
        private Name $lastName,
        private Roles $roles,
        private DateTimeImmutable $created,
        private bool $enabled,
        private ?string $image = null,
        private ?string $experience = null,
        private ?string $education = null,
        private ?Token $confirmationToken = null,
        private ?DateTimeImmutable $expirationDate = null
    ) {
    }

    public static function create(
        Uuid $id,
        Email $email,
        Password $password,
        Name $firstName,
        Name $lastName,
        Roles $roles,
        DateTimeImmutable $created,
        bool $enabled,
        ?string $image = null,
        ?string $experience = null,
        ?string $education = null,
        ?Token $confirmationToken = null,
        ?DateTimeImmutable $expirationDate = null
    ): self {
        $instance = new static(
            $id,
            $email,
            $password,
            $firstName,
            $lastName,
            $roles,
            $created,
            $enabled,
            $image,
            $experience,
            $education,
            $confirmationToken,
            $expirationDate
        );

        $instance->recordThat(
            new UserCreatedDomainEvent(
                $instance->getId()->toString(),
                $instance->getEmail()->toString(),
                $instance->getFirstName()->toString(),
                $instance->getLastName()->toString(),
                $instance->getConfirmationToken()->toString(),
            )
        );

        return $instance;
    }

    private function recordThat(DomainEvent $event): void
    {
        $this->eventStream[] = $event;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getFirstName(): Name
    {
        return $this->firstName;
    }

    public function getLastName(): Name
    {
        return $this->lastName;
    }

    public function getConfirmationToken(): ?Token
    {
        return $this->confirmationToken;
    }

    public function pullDomainEvents(): array
    {
        $events = $this->eventStream ?: [];
        $this->eventStream = [];

        return $events;
    }

    public function setEmail(Email $email): void
    {
        $this->email = $email;
    }

    public function setPassword(Password $password): void
    {
        $this->password = $password;
    }

    public function setFirstName(Name $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function setLastName(Name $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function setRoles(Roles $roles): void
    {
        $this->roles = $roles;
    }

    public function setImage(?string $image): void
    {
        $this->image = $image;
    }

    public function setExperience(?string $experience): void
    {
        $this->experience = $experience;
    }

    public function setCreated(DateTimeImmutable $created): void
    {
        $this->created = $created;
    }

    public function setEducation(?string $education): void
    {
        $this->education = $education;
    }

    public function getPassword(): Password
    {
        return $this->password;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function getExperience(): ?string
    {
        return $this->experience;
    }

    public function getCreated(): ?DateTimeImmutable
    {
        return $this->created;
    }

    public function getEducation(): ?string
    {
        return $this->education;
    }

    public function setConfirmationToken(?Token $confirmationToken): void
    {
        $this->confirmationToken = $confirmationToken;
    }

    public function ensureUserEnabled(): void
    {
        if (false === $this->getEnabled()) {
            throw new UserNotEnabledException();
        }
    }

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function isInRole(Role $role): bool
    {
        return $this->roles->contains($role);
    }

    public function getExpirationDate(): ?DateTimeImmutable
    {
        return $this->expirationDate;
    }

    public function setExpirationDate(?DateTimeImmutable $expirationDate): void
    {
        $this->expirationDate = $expirationDate;
    }

    public function validateConfirmationToken(Token $tokenFromRequest): void
    {
        if (null === $this->getConfirmationToken()) {
            throw new ConfirmationTokenNotFoundException();
        }
        if (true === $this->isConfirmationTokenExpired()) {
            throw new ConfirmationTokenIsExpiredException();
        }
        if (false === $this->confirmationTokenEqualsTo($tokenFromRequest)) {
            throw new IncorrectConfirmationTokenException();
        }
    }

    public function isConfirmationTokenExpired()
    {
        return $this->expirationDate <= new DateTime();
    }

    public function confirmationTokenEqualsTo(Token $confirmationToken): bool
    {
        return $this->confirmationToken->equalsTo($confirmationToken);
    }

    public function ensureUsersAreNotEqual(User $otherUser): void
    {
        if (true === $this->idEqualsTo($otherUser->getId())) {
            throw new UsersAreEqualException();
        }
    }

    public function idEqualsTo(Uuid $id): bool
    {
        return $this->id->equalsTo($id);
    }
}
