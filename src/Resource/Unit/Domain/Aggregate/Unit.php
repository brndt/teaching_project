<?php

declare(strict_types=1);

namespace LaSalle\StudentTeacher\Resource\Unit\Domain\Aggregate;

use DateTimeImmutable;
use LaSalle\StudentTeacher\Shared\Domain\ValueObject\Status;
use LaSalle\StudentTeacher\Shared\Domain\ValueObject\Uuid;

final class Unit
{
    public function __construct(
        private Uuid $id,
        private Uuid $courseId,
        private string $name,
        private ?string $description,
        private string $level,
        private DateTimeImmutable $created,
        private ?DateTimeImmutable $modified,
        private Status $status
    ) {
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): void
    {
        $this->id = $id;
    }

    public function getCourseId(): Uuid
    {
        return $this->courseId;
    }

    public function setCourseId(Uuid $courseId): void
    {
        $this->courseId = $courseId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getLevel(): string
    {
        return $this->level;
    }

    public function setLevel(string $level): void
    {
        $this->level = $level;
    }

    public function getCreated(): DateTimeImmutable
    {
        return $this->created;
    }

    public function setCreated(DateTimeImmutable $created): void
    {
        $this->created = $created;
    }

    public function getModified(): ?DateTimeImmutable
    {
        return $this->modified;
    }

    public function setModified(?DateTimeImmutable $modified): void
    {
        $this->modified = $modified;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function setStatus(Status $status): void
    {
        $this->status = $status;
    }
}
