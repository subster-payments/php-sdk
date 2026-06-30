<?php

declare(strict_types=1);

namespace Subster\PhpSdk\Concerns;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionType;

class Data
{
    public static function from(array $data = []): static
    {
        $reflection = new ReflectionClass(static::class);
        $params = [];

        foreach ($reflection->getConstructor()->getParameters() as $param) {
            $name = $param->getName();

            if (array_key_exists($name, $data)) {
                $params[$name] = self::normalizeConstructorValue($param->getType(), $data[$name]);

                continue;
            }

            if ($param->isDefaultValueAvailable()) {
                $params[$name] = $param->getDefaultValue();

                continue;
            }

            $type = $param->getType();

            if ($type instanceof ReflectionNamedType && $type->allowsNull()) {
                $params[$name] = null;

                continue;
            }

            throw new InvalidArgumentException("Missing required parameter: {$name}");
        }

        return new static(...$params);
    }

    protected static function normalizeConstructorValue(?ReflectionType $type, mixed $value): mixed
    {
        if ( ! $type instanceof ReflectionNamedType || $type->isBuiltin() || ! is_array($value)) {
            return $value;
        }

        $className = $type->getName();

        if ( ! is_subclass_of($className, self::class)) {
            return $value;
        }

        return $className::from($value);
    }

    public function toArray(): array
    {
        return array_map(
            fn (mixed $value): mixed => $this->normalizeValue($value),
            get_object_vars($this),
        );
    }

    protected function normalizeValue(mixed $value): mixed
    {
        if ($value instanceof self) {
            return $value->toArray();
        }

        if ($value instanceof Collection) {
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
