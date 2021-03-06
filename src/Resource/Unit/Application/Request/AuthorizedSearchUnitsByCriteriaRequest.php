<?php

declare(strict_types=1);

namespace LaSalle\StudentTeacher\Resource\Unit\Application\Request;

final class AuthorizedSearchUnitsByCriteriaRequest
{
    public function __construct(
        private string $requestAuthorId,
        private ?string $courseId,
        private ?string $orderBy,
        private ?string $order,
        private ?string $operator,
        private ?int $offset,
        private ?int $limit
    ) {
    }

    public function getRequestAuthorId(): string
    {
        return $this->requestAuthorId;
    }

    public function getCourseId(): ?string
    {
        return $this->courseId;
    }

    public function getOrderBy(): ?string
    {
        return $this->orderBy;
    }

    public function getOrder(): ?string
    {
        return $this->order;
    }

    public function getOperator(): ?string
    {
        return $this->operator;
    }

    public function getOffset(): ?int
    {
        return $this->offset;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }
}
