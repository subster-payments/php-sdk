<?php

declare(strict_types=1);

namespace Subster\PhpSdk\Resources;

use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\BaseResource;
use Subster\PhpSdk\DataObjects\CheckoutSessionData;
use Subster\PhpSdk\DataObjects\CreateCheckoutSessionData;
use Subster\PhpSdk\Requests\CreateCheckoutSessionRequest;

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
}
