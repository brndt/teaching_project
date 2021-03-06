<?php

declare(strict_types=1);

namespace Test\LaSalle\StudentTeacher\Resource\Unit\Application;

use LaSalle\StudentTeacher\Resource\Course\Domain\Repository\CourseRepository;
use LaSalle\StudentTeacher\Resource\Unit\Application\Request\UnauthorizedSearchUnitsByCriteriaRequest;
use LaSalle\StudentTeacher\Resource\Unit\Application\Response\UnitCollectionResponse;
use LaSalle\StudentTeacher\Resource\Unit\Application\Response\UnitResponse;
use LaSalle\StudentTeacher\Resource\Unit\Application\Service\UnauthorizedSearchUnitsByCriteriaService;
use LaSalle\StudentTeacher\Resource\Unit\Domain\Aggregate\Unit;
use LaSalle\StudentTeacher\Resource\Unit\Domain\Repository\UnitRepository;
use LaSalle\StudentTeacher\User\User\Domain\Repository\UserRepository;
use PHPUnit\Framework\TestCase;
use Test\LaSalle\StudentTeacher\Resource\Unit\Domain\UnitBuilder;

final class UnauthorizedSearchUnitsByCriteriaServiceTest extends TestCase
{
    private UnauthorizedSearchUnitsByCriteriaService $unauthorizedSearchUnitsByCriteriaService;
    private $userRepository;
    private $unitRepository;
    private $courseRepository;

    public function setUp(): void
    {
        $this->unitRepository = $this->createMock(UnitRepository::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->courseRepository = $this->createMock(CourseRepository::class);

        $this->unauthorizedSearchUnitsByCriteriaService = new UnauthorizedSearchUnitsByCriteriaService(
            $this->unitRepository,
            $this->userRepository,
            $this->courseRepository,
        );
    }

    public function testWhenRequestIsValidThenSearchUnits()
    {
        $request = new UnauthorizedSearchUnitsByCriteriaRequest(
            [['field' => 'status', 'operator' => '=', 'value' => 'published']],
            null,
            null,
            null,
            null,
            null
        );

        $units = [(new UnitBuilder())->build(), (new UnitBuilder())->build()];
        $expectedUnitCollectionResponse = new UnitCollectionResponse(...$this->buildResponse(...$units));

        $this->unitRepository
            ->expects(self::once())
            ->method('matching')
            ->willReturn($units);

        $actualUnitCollectionResponse = ($this->unauthorizedSearchUnitsByCriteriaService)($request);
        $this->assertEquals($expectedUnitCollectionResponse, $actualUnitCollectionResponse);
    }


    private function buildResponse(Unit ...$units): array
    {
        return array_map(
            static function (Unit $unit) {
                return new UnitResponse(
                    $unit->getId()->toString(),
                    $unit->getCourseId()->toString(),
                    $unit->getName(),
                    $unit->getDescription(),
                    $unit->getLevel(),
                    $unit->getCreated(),
                    $unit->getModified(),
                    $unit->getStatus()->value(),
                );
            },
            $units
        );
    }

}
