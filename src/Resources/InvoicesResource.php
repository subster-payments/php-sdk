<?php

declare(strict_types=1);

namespace Subster\PhpSdk\Resources;

use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\BaseResource;
use Subster\PhpSdk\DataObjects\CreateInvoiceRefundData;
use Subster\PhpSdk\DataObjects\InvoiceListData;
use Subster\PhpSdk\DataObjects\ListInvoicesData;
use Subster\PhpSdk\DataObjects\RefundData;
use Subster\PhpSdk\Requests\CreateInvoiceRefundRequest;
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

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function refund(string $invoice, CreateInvoiceRefundData $data): RefundData
    {
        return $this->connector->send(
            new CreateInvoiceRefundRequest($invoice, $data)
        )->dtoOrFail();
    }
}
