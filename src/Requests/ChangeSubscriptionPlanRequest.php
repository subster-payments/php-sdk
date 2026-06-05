<?php

declare(strict_types=1);

namespace Subster\PhpSdk\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;
use Saloon\Traits\Request\HasConnector;
use Subster\PhpSdk\DataObjects\ChangeSubscriptionPlanData;
use Subster\PhpSdk\DataObjects\SubscriptionPlanChangeData;
use Subster\PhpSdk\SubsterConnector;

class ChangeSubscriptionPlanRequest extends Request implements HasBody
{
    use HasConnector;
    use HasJsonBody;

    protected string $connector = SubsterConnector::class;

    protected Method $method = Method::POST;

    public function __construct(
        public readonly string $subscription,
        public readonly ChangeSubscriptionPlanData $data,
    ) {}

    public function resolveEndpoint(): string
    {
        return 'subscriptions/'.$this->subscription.'/change-plan';
    }

    protected function defaultBody(): array
    {
        return [
            'plan' => $this->data->plan,
            ...($this->data->success_url ? ['success_url' => $this->data->success_url] : []),
            ...($this->data->cancel_url ? ['cancel_url' => $this->data->cancel_url] : []),
        ];
    }

    public function createDtoFromResponse(Response $response): SubscriptionPlanChangeData
    {
        return SubscriptionPlanChangeData::fromSaloon($response->json());
    }
}
