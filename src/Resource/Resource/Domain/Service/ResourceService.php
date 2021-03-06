<?php

declare(strict_types=1);

namespace LaSalle\StudentTeacher\Resource\Resource\Domain\Service;

use LaSalle\StudentTeacher\Resource\Resource\Domain\Aggregate\Resource;
use LaSalle\StudentTeacher\Resource\Resource\Domain\Aggregate\TestResource;
use LaSalle\StudentTeacher\Resource\Resource\Domain\Aggregate\VideoResource;
use LaSalle\StudentTeacher\Resource\Resource\Domain\Exception\ResourceNotFoundException;
use LaSalle\StudentTeacher\Resource\Resource\Domain\Repository\ResourceRepository;
use LaSalle\StudentTeacher\Shared\Domain\ValueObject\Uuid;

final class ResourceService
{
    public function __construct(private ResourceRepository $repository)
    {
    }

    /**
     * @return VideoResource|TestResource
     */
    public function findResource(Uuid $id): Resource
    {
        $resource = $this->repository->ofId($id);
        if (null === $resource) {
            throw new ResourceNotFoundException();
        }
        return $resource;
    }
}
