<?php

declare(strict_types=1);

namespace Subster\PhpSdk\Requests;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Request\HasConnector;
use Subster\PhpSdk\DataObjects\InvoiceListData;
use Subster\PhpSdk\DataObjects\ListInvoicesData;
use Subster\PhpSdk\SubsterConnector;

class ListInvoicesRequest extends Request
{
    use HasConnector;

    protected string $connector = SubsterConnector::class;

    protected Method $method = Method::GET;

    public function __construct(public readonly ?ListInvoicesData $filters = null) {}

    public function resolveEndpoint(): string
    {
        return 'invoices';
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'limit' => $this->filters?->limit,
            'starting_after' => $this->filters?->starting_after,
            'ending_before' => $this->filters?->ending_before,
            'customer' => $this->filters?->customer,
            'subscription' => $this->filters?->subscription,
            'paid_at[gte]' => $this->formatDateFilter($this->filters?->paid_at_gte),
            'paid_at[lte]' => $this->formatDateFilter($this->filters?->paid_at_lte),
        ], fn (mixed $value): bool => $value !== null);
    }

    private function formatDateFilter(DateTimeInterface|string|null $value): ?string
    {
        if ($value instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($value)
                ->setTimezone(new DateTimeZone('UTC'))
                ->format(DateTimeInterface::ATOM);
        }

        return $value;
    }

    public function createDtoFromResponse(Response $response): InvoiceListData
    {
        return InvoiceListData::fromSaloon($response->json());
    }
}
