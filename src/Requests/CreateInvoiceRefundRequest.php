<?php

declare(strict_types=1);

namespace Subster\PhpSdk\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;
use Saloon\Traits\Request\HasConnector;
use Subster\PhpSdk\DataObjects\CreateInvoiceRefundData;
use Subster\PhpSdk\DataObjects\RefundData;
use Subster\PhpSdk\SubsterConnector;

class CreateInvoiceRefundRequest extends Request implements HasBody
{
    use HasConnector;
    use HasJsonBody;

    protected string $connector = SubsterConnector::class;

    protected Method $method = Method::POST;

    public function __construct(
        public readonly string $invoice,
        public readonly CreateInvoiceRefundData $data,
    ) {}

    public function resolveEndpoint(): string
    {
        return 'invoices/'.$this->invoice.'/refunds';
    }

    protected function defaultHeaders(): array
    {
        return ['Idempotency-Key' => $this->data->idempotencyKey];
    }

    protected function defaultBody(): array
    {
        return [
            ...($this->data->amount !== null ? ['amount' => $this->data->amount] : []),
            'reason' => $this->data->reason,
        ];
    }

    public function createDtoFromResponse(Response $response): RefundData
    {
        return RefundData::fromSaloon($response->json());
    }
}
