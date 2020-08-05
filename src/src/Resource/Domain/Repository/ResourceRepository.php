<?php

declare(strict_types=1);

namespace LaSalle\StudentTeacher\Resource\Domain\Repository;

use LaSalle\StudentTeacher\Resource\Domain\Aggregate\Resource;
use LaSalle\StudentTeacher\Shared\Domain\Criteria\Criteria;
use LaSalle\StudentTeacher\Shared\Domain\ValueObject\Uuid;

interface ResourceRepository
{
    public function save(Resource $category): void;

    public function ofId(Uuid $id): ?Resource;

    public function nextIdentity(): Uuid;

    public function matching(Criteria $criteria): array;
}