<?php

declare(strict_types=1);

namespace LaSalle\StudentTeacher\User\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use LaSalle\StudentTeacher\User\Domain\ValueObject\Email;
use LaSalle\StudentTeacher\User\Domain\ValueObject\Name;

final class NameType extends Type
{
    const NAME = 'name';

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return new Name($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (false == is_string($value)) {
            return $value->toString();
        }
        return $value;
    }

    public function getName()
    {
        return self::NAME;
    }

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'VARCHAR';
    }
}