<?php

declare(strict_types=1);

namespace Subster\PhpSdk\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;
use Saloon\Traits\Request\HasConnector;
use Subster\PhpSdk\DataObjects\CheckoutSessionData;
use Subster\PhpSdk\DataObjects\CreateCheckoutSessionData;
use Subster\PhpSdk\SubsterConnector;

class CreateCheckoutSessionRequest extends Request implements HasBody
{
    use HasConnector;
    use HasJsonBody;

    protected string $connector = SubsterConnector::class;

    protected Method $method = Method::POST;

    public function __construct(public readonly CreateCheckoutSessionData $data) {}

    public function resolveEndpoint(): string
    {
        return 'checkout/session';
    }

    protected function defaultBody(): array
    {
        return [
            'customer' => $this->data->customer,
            'items' => $this->data->items,
            'success_url' => $this->data->success_url,
            ...($this->data->cancel_url ? ['cancel_url' => $this->data->cancel_url] : []),
            ...($this->data->subscription_data ? ['subscription_data' => $this->data->subscription_data->toArray()] : []),
        ];
    }

    public function createDtoFromResponse(Response $response): CheckoutSessionData
    {
        return CheckoutSessionData::fromSaloon($response->json());
    }
}
