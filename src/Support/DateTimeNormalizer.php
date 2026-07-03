<?php

declare(strict_types=1);

namespace Subster\PhpSdk\Support;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

final class DateTimeNormalizer
{
    public static function parse(DateTimeInterface|string|int|float $value): DateTimeImmutable
    {
        if ($value instanceof DateTimeInterface) {
            return self::immutable($value)->setTimezone(self::utc());
        }

        if (is_int($value) || is_float($value) || (is_string($value) && ctype_digit($value))) {
            return (new DateTimeImmutable('@'.(string) (int) $value))->setTimezone(self::utc());
        }

        return (new DateTimeImmutable((string) $value, self::utc()))->setTimezone(self::utc());
    }

    public static function serialize(DateTimeInterface $value): string
    {
        return self::immutable($value)
            ->setTimezone(self::utc())
            ->format(DateTimeInterface::ATOM);
    }

    private static function immutable(DateTimeInterface $value): DateTimeImmutable
    {
        return $value instanceof DateTimeImmutable
            ? $value
            : DateTimeImmutable::createFromInterface($value);
    }

    private static function utc(): DateTimeZone
    {
        return new DateTimeZone('UTC');
    }
}
