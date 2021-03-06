<?php

declare(strict_types=1);

namespace LaSalle\StudentTeacher\Shared\Domain\ValueObject;

use LaSalle\StudentTeacher\Shared\Domain\Utils;
use LaSalle\StudentTeacher\User\User\Domain\ValueObject\Role;
use ReflectionClass;
use Stringable;

use function in_array;
use function Lambdish\Phunctional\reindex;

abstract class Enum implements Stringable
{
    protected static array $cache = [];

    public function __construct(protected $value)
    {
        $this->ensureIsBetweenAcceptedValues($value);
    }

    private function ensureIsBetweenAcceptedValues($value): void
    {
        if (!in_array($value, static::values(), true)) {
            $this->throwExceptionForInvalidValue($value);
        }
    }

    public static function values(): array
    {
        $class = static::class;

        if (!isset(self::$cache[$class])) {
            $reflected = new ReflectionClass($class);
            self::$cache[$class] = reindex(self::keysFormatter(), $reflected->getConstants());
        }

        return self::$cache[$class];
    }

    private static function keysFormatter(): callable
    {
        return static fn($unused, string $key): string => Utils::toCamelCase(strtolower($key));
    }

    abstract protected function throwExceptionForInvalidValue($value);

    public static function __callStatic(string $name, $args)
    {
        return new static(self::values()[$name]);
    }

    public static function fromString(string $value): Enum
    {
        return new static($value);
    }

    public static function random(): self
    {
        return new static(self::randomValue());
    }

    public static function randomValue()
    {
        return self::values()[array_rand(self::values())];
    }

    public function equals(Enum $other): bool
    {
        return $other == $this;
    }

    public function __toString(): string
    {
        return (string)$this->value();
    }

    public function value()
    {
        return $this->value;
    }
}
