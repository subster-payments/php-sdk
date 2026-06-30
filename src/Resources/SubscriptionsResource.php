<?php

declare(strict_types=1);

namespace Subster\PhpSdk\Resources;

use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\BaseResource;
use Subster\PhpSdk\DataObjects\ChangeSubscriptionPlanData;
use Subster\PhpSdk\DataObjects\RecordSubscriptionUsageEventData;
use Subster\PhpSdk\DataObjects\SubscriptionPlanChangeData;
use Subster\PhpSdk\DataObjects\SubscriptionUsageEventData;
use Subster\PhpSdk\Requests\ChangeSubscriptionPlanRequest;
use Subster\PhpSdk\Requests\RecordSubscriptionUsageEventRequest;

class SubscriptionsResource extends BaseResource
{
    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function changePlan(string $subscription, ChangeSubscriptionPlanData $data): SubscriptionPlanChangeData
    {
        return $this->connector->send(
            new ChangeSubscriptionPlanRequest($subscription, $data)
        )->dtoOrFail();
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function recordUsage(string $subscription, RecordSubscriptionUsageEventData $data): SubscriptionUsageEventData
    {
        return $this->connector->send(
            new RecordSubscriptionUsageEventRequest($subscription, $data)
        )->dtoOrFail();
    }
}
