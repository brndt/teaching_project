<?php

declare(strict_types=1);

namespace LaSalle\StudentTeacher\Shared\Domain\Event;

interface DomainEventBus
{
    public function dispatch(DomainEvent $event, string $eventName = null);
}