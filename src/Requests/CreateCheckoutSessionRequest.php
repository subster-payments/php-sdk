<?php

declare(strict_types=1);

namespace Subster\PhpSdk\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;
use Saloon\Traits\Request\HasConnector;
use Subster\PhpSdk\Concerns\Data;
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
            'items' => $this->items(),
            'success_url' => $this->data->success_url,
            ...($this->data->cancel_url ? ['cancel_url' => $this->data->cancel_url] : []),
            ...($this->data->subscription_data ? ['subscription_data' => $this->data->subscription_data->toArray()] : []),
        ];
    }

    protected function items(): array
    {
        return array_map(
            fn (mixed $item): mixed => $this->normalizeItem($item),
            $this->data->items,
        );
    }

    protected function normalizeItem(mixed $item): mixed
    {
        if ($item instanceof Data) {
            $item = $item->toArray();
        }

        if (is_array($item) && array_key_exists('quantity', $item) && $item['quantity'] === null) {
            unset($item['quantity']);
        }

        return $item;
    }

    public function createDtoFromResponse(Response $response): CheckoutSessionData
    {
        return CheckoutSessionData::fromSaloon($response->json());
    }
}
