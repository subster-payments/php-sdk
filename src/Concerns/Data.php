<?php

declare(strict_types=1);

namespace Subster\PhpSdk\Concerns;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionNamedType;

class Data
{
    public static function from(array $data = []): static
    {
        $reflection = new ReflectionClass(static::class);
        $params = [];

        foreach ($reflection->getConstructor()->getParameters() as $param) {
            $name = $param->getName();

            if (array_key_exists($name, $data)) {
                $params[$name] = $data[$name];

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

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
