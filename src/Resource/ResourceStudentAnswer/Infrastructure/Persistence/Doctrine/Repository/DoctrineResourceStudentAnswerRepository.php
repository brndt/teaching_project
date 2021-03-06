<?php

declare(strict_types=1);

namespace LaSalle\StudentTeacher\Resource\ResourceStudentAnswer\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\ORM\EntityManagerInterface;
use LaSalle\StudentTeacher\Resource\ResourceStudentAnswer\Domain\Aggregate\ResourceStudentAnswer;
use LaSalle\StudentTeacher\Resource\ResourceStudentAnswer\Domain\Repository\ResourceStudentAnswerRepository;
use LaSalle\StudentTeacher\Shared\Domain\Criteria\Criteria;
use LaSalle\StudentTeacher\Shared\Domain\ValueObject\Uuid;
use LaSalle\StudentTeacher\Shared\Infrastructure\Persistence\Doctrine\DoctrineCriteriaConverter;

final class DoctrineResourceStudentAnswerRepository implements ResourceStudentAnswerRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(ResourceStudentAnswer $resourceStudentAnswer): void
    {
        $this->entityManager->persist($resourceStudentAnswer);
        $this->entityManager->flush();
    }

    /**
     * @return object|ResourceStudentAnswer|null
     */
    public function ofId(Uuid $id): ?ResourceStudentAnswer
    {
        return $this->entityManager->getRepository(ResourceStudentAnswer::class)->find($id);
    }

    public function nextIdentity(): Uuid
    {
        return Uuid::generate();
    }

    public function matching(Criteria $criteria): array
    {
        $doctrineCriteria = DoctrineCriteriaConverter::convert($criteria);
        return $this->entityManager->getRepository(ResourceStudentAnswer::class)->matching(
            $doctrineCriteria
        )->toArray();
    }
}
