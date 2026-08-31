<?php

declare(strict_types=1);

namespace Subster\PhpSdk\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;
use Saloon\Traits\Request\HasConnector;
use Subster\PhpSdk\DataObjects\PreviewSubscriptionPlanChangeData;
use Subster\PhpSdk\DataObjects\SubscriptionPlanChangeQuoteData;
use Subster\PhpSdk\SubsterConnector;

class PreviewSubscriptionPlanChangeRequest extends Request implements HasBody
{
    use HasConnector;
    use HasJsonBody;

    protected string $connector = SubsterConnector::class;

    protected Method $method = Method::POST;

    public function __construct(
        public readonly string $subscription,
        public readonly PreviewSubscriptionPlanChangeData $data,
    ) {}

    public function resolveEndpoint(): string
    {
        return 'subscriptions/'.$this->subscription.'/plan-change-quotes';
    }

    protected function defaultBody(): array
    {
        return [
            'plan' => $this->data->plan,
            ...($this->data->proration_behavior !== null ? ['proration_behavior' => $this->data->proration_behavior->value] : []),
            ...($this->data->payment_strategy !== null ? ['payment_strategy' => $this->data->payment_strategy->value] : []),
        ];
    }

    public function createDtoFromResponse(Response $response): SubscriptionPlanChangeQuoteData
    {
        return SubscriptionPlanChangeQuoteData::fromSaloon($response->json());
    }
}
