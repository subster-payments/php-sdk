<?php

declare(strict_types=1);

namespace Subster\PhpSdk\Resources;

use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\BaseResource;
use Subster\PhpSdk\DataObjects\CreateCustomerData;
use Subster\PhpSdk\DataObjects\CustomerData;
use Subster\PhpSdk\DataObjects\UpdateCustomerData;
use Subster\PhpSdk\Requests\CreateCustomerRequest;
use Subster\PhpSdk\Requests\UpdateCustomerRequest;

class CustomersResource extends BaseResource
{
    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function create(CreateCustomerData $data): CustomerData
    {
        return $this->connector->send(
            new CreateCustomerRequest($data)
        )->dtoOrFail();
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function update(UpdateCustomerData $data): CustomerData
    {
        return $this->connector->debug(die: true)->send(
            new UpdateCustomerRequest($data)
        )->dtoOrFail();
    }
}
