<?php

declare(strict_types=1);

namespace Subster\PhpSdk\Concerns;

use ArrayIterator;
use BackedEnum;
use Closure;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use IteratorAggregate;

class Collection implements IteratorAggregate
{
    public function __construct(
        public array $items,
    ) {}

    public function toArray(): array
    {
        return array_map(
            fn (mixed $value): mixed => $this->normalizeValue($value),
            $this->items,
        );
    }

    public static function make(array $items, ?Closure $closure = null): static
    {
        return new static(
            $closure ? array_map($closure, $items) : $items
        );
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    protected function normalizeValue(mixed $value): mixed
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        if ($value instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($value)
                ->setTimezone(new DateTimeZone('UTC'))
                ->format(DateTimeInterface::ATOM);
        }

        if ($value instanceof Data || $value instanceof self) {
            return $value->toArray();
        }

        if (is_array($value)) {
            return array_map(
                fn (mixed $item): mixed => $this->normalizeValue($item),
                $value,
            );
        }

        return $value;
    }
}
