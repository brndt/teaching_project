<?php

declare(strict_types=1);

namespace LaSalle\StudentTeacher\User\Domain\Aggregate;

use LaSalle\StudentTeacher\Shared\Domain\ValueObject\Uuid;
use LaSalle\StudentTeacher\User\Domain\ValueObject\State\State;

final class UserConnection
{
    private Uuid $studentId;
    private Uuid $teacherId;
    private State $state;
    private Uuid $specifierId;

    public function __construct(
        Uuid $studentId,
        Uuid $teacherId,
        State $state,
        Uuid $specifierId
    )
    {
        $this->studentId = $studentId;
        $this->teacherId = $teacherId;
        $this->state = $state;
        $this->specifierId = $specifierId;
    }

    public function getStudentId(): Uuid
    {
        return $this->studentId;
    }

    public function getTeacherId(): Uuid
    {
        return $this->teacherId;
    }

    public function getState(): State
    {
        return $this->state;
    }

    public function setStudentId(Uuid $studentId): void
    {
        $this->studentId = $studentId;
    }

    public function setTeacherId(Uuid $teacherId): void
    {
        $this->teacherId = $teacherId;
    }

    public function setState(State $newStatus, bool $ifSpecifierChanged): void
    {
        $this->state->tryTransition($newStatus, $ifSpecifierChanged);
        $this->state = $newStatus;
    }

    public function setSpecifierId(Uuid $specifierId): void
    {
        $this->specifierId = $specifierId;
    }

    public function getSpecifierId(): Uuid
    {
        return $this->specifierId;
    }

}