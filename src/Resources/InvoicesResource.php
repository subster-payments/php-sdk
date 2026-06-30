<?php

declare(strict_types=1);

namespace Subster\PhpSdk\Resources;

use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\BaseResource;
use Subster\PhpSdk\DataObjects\InvoiceListData;
use Subster\PhpSdk\DataObjects\ListInvoicesData;
use Subster\PhpSdk\Requests\ListInvoicesRequest;

class InvoicesResource extends BaseResource
{
    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function all(?ListInvoicesData $filters = null): InvoiceListData
    {
        return $this->connector->send(
            new ListInvoicesRequest($filters)
        )->dtoOrFail();
    }
}
