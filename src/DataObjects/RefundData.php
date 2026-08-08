<?php

declare(strict_types=1);

namespace Subster\PhpSdk\DataObjects;

use DateTimeImmutable;
use Subster\PhpSdk\Concerns\Data;
use Subster\PhpSdk\Enums\Currency;
use Subster\PhpSdk\Enums\RefundStatus;
use Subster\PhpSdk\Support\DateTimeNormalizer;

class RefundData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly RefundStatus $status,
        public readonly string $source,
        public readonly string $invoice,
        public readonly string $charge,
        public readonly float $amount,
        public readonly Currency $currency,
        public readonly ?string $reason,
        public readonly ?string $idempotency_key,
        public readonly ?string $provider_reference,
        public readonly ?string $failure_message,
        public readonly ?DateTimeImmutable $refunded_at,
        public readonly DateTimeImmutable $created_at,
        public readonly DateTimeImmutable $updated_at,
    ) {}

    /**
     * @param  array<string, mixed>  $response
     */
    public static function fromSaloon(array $response): self
    {
        return new self(
            id: strval($response['id']),
            status: RefundStatus::from(strval($response['status'])),
            source: strval($response['source']),
            invoice: strval($response['invoice']),
            charge: strval($response['charge']),
            amount: floatval($response['amount']),
            currency: Currency::from(strval($response['currency'])),
            reason: isset($response['reason']) ? strval($response['reason']) : null,
            idempotency_key: isset($response['idempotency_key']) ? strval($response['idempotency_key']) : null,
            provider_reference: isset($response['provider_reference']) ? strval($response['provider_reference']) : null,
            failure_message: isset($response['failure_message']) ? strval($response['failure_message']) : null,
            refunded_at: isset($response['refunded_at']) ? DateTimeNormalizer::parse($response['refunded_at']) : null,
            created_at: DateTimeNormalizer::parse($response['created_at']),
            updated_at: DateTimeNormalizer::parse($response['updated_at']),
        );
    }
}
