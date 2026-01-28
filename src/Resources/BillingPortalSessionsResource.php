<?php

declare(strict_types=1);

namespace Subster\PhpSdk\Resources;

use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\BaseResource;
use Subster\PhpSdk\DataObjects\BillingPortalSessionData;
use Subster\PhpSdk\DataObjects\CreateBillingPortalSessionData;
use Subster\PhpSdk\Requests\CreateBillingPortalSessionRequest;

class BillingPortalSessionsResource extends BaseResource
{
    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function create(CreateBillingPortalSessionData $data): BillingPortalSessionData
    {
        return $this->connector->send(
            new CreateBillingPortalSessionRequest($data)
        )->dtoOrFail();
    }
}
