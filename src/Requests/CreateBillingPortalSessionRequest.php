<?php

declare(strict_types=1);

namespace Subster\PhpSdk\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;
use Saloon\Traits\Request\HasConnector;
use Subster\PhpSdk\DataObjects\BillingPortalSessionData;
use Subster\PhpSdk\DataObjects\CreateBillingPortalSessionData;
use Subster\PhpSdk\SubsterConnector;

class CreateBillingPortalSessionRequest extends Request implements HasBody
{
    use HasConnector;
    use HasJsonBody;

    protected string $connector = SubsterConnector::class;

    protected Method $method = Method::POST;

    public function __construct(public readonly CreateBillingPortalSessionData $data) {}

    public function resolveEndpoint(): string
    {
        return 'billing-portal/session';
    }

    protected function defaultBody(): array
    {
        return [
            'customer' => $this->data->customer,
            ...($this->data->return_url ? ['return_url' => $this->data->return_url] : []),
        ];
    }

    public function createDtoFromResponse(Response $response): BillingPortalSessionData
    {
        return BillingPortalSessionData::fromSaloon($response->json());
    }
}
