<?php

declare(strict_types=1);

namespace Subster\PhpSdk\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;
use Saloon\Traits\Request\HasConnector;
use Subster\PhpSdk\DataObjects\RecordSubscriptionUsageEventData;
use Subster\PhpSdk\DataObjects\SubscriptionUsageEventData;
use Subster\PhpSdk\SubsterConnector;

class RecordSubscriptionUsageEventRequest extends Request implements HasBody
{
    use HasConnector;
    use HasJsonBody;

    protected string $connector = SubsterConnector::class;

    protected Method $method = Method::POST;

    public function __construct(
        public readonly string $subscription,
        public readonly RecordSubscriptionUsageEventData $data,
    ) {}

    public function resolveEndpoint(): string
    {
        return 'subscriptions/'.$this->subscription.'/usage-events';
    }

    protected function defaultBody(): array
    {
        return $this->data->toArray();
    }

    public function createDtoFromResponse(Response $response): SubscriptionUsageEventData
    {
        return SubscriptionUsageEventData::fromSaloon($response->json());
    }
}
