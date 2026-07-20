<?php

declare(strict_types=1);

namespace Subster\PhpSdk\Resources;

use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\BaseResource;
use Subster\PhpSdk\DataObjects\CheckoutSessionData;
use Subster\PhpSdk\DataObjects\CheckoutSessionStatusData;
use Subster\PhpSdk\DataObjects\CreateCheckoutSessionData;
use Subster\PhpSdk\Requests\CancelCheckoutSessionRequest;
use Subster\PhpSdk\Requests\CreateCheckoutSessionRequest;
use Subster\PhpSdk\Requests\GetCheckoutSessionRequest;

class CheckoutSessionsResource extends BaseResource
{
    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function create(CreateCheckoutSessionData $data): CheckoutSessionData
    {
        return $this->connector->send(
            new CreateCheckoutSessionRequest($data)
        )->dtoOrFail();
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function get(string $session): CheckoutSessionStatusData
    {
        return $this->connector->send(
            new GetCheckoutSessionRequest($session)
        )->dtoOrFail();
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function cancel(string $session): CheckoutSessionStatusData
    {
        return $this->connector->send(
            new CancelCheckoutSessionRequest($session)
        )->dtoOrFail();
    }
}
