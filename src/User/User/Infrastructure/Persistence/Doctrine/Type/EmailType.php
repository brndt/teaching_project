<?php

declare(strict_types=1);

namespace LaSalle\StudentTeacher\User\User\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use LaSalle\StudentTeacher\User\User\Domain\ValueObject\Email;

final class EmailType extends Type
{
    const NAME = 'email';

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return new Email($value);
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