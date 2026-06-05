<?php

declare(strict_types=1);

namespace Subster\PhpSdk\Resources;

use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\BaseResource;
use Subster\PhpSdk\DataObjects\ChangeSubscriptionPlanData;
use Subster\PhpSdk\DataObjects\SubscriptionPlanChangeData;
use Subster\PhpSdk\Requests\ChangeSubscriptionPlanRequest;

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
}
