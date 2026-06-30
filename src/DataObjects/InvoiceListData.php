<?php

declare(strict_types=1);

namespace Subster\PhpSdk\DataObjects;

use Subster\PhpSdk\Concerns\Collection;
use Subster\PhpSdk\Concerns\Data;

class InvoiceListData extends Data
{
    public function __construct(
        public readonly string $object,
        public readonly string $url,
        public readonly bool $has_more,
        public readonly Collection $data,
    ) {}

    public static function fromSaloon(array $response): self
    {
        return new self(
            object: strval($response['object']),
            url: strval($response['url']),
            has_more: boolval($response['has_more']),
            data: Collection::make(
                $response['data'],
                fn (array $invoice): InvoiceData => InvoiceData::fromSaloon($invoice),
            ),
        );
    }
}
