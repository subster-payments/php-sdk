<?php

declare(strict_types=1);

namespace Subster\PhpSdk\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;
use Saloon\Traits\Request\HasConnector;
use Subster\PhpSdk\DataObjects\CreateCustomerData;
use Subster\PhpSdk\DataObjects\CustomerData;
use Subster\PhpSdk\SubsterConnector;

class CreateCustomerRequest extends Request implements HasBody
{
    use HasConnector;
    use HasJsonBody;

    protected string $connector = SubsterConnector::class;

    protected Method $method = Method::POST;

    public function __construct(public readonly CreateCustomerData $data) {}

    public function resolveEndpoint(): string
    {
        return 'customers';
    }

    protected function defaultBody(): array
    {
        return [
            ...($this->data->name !== null ? ['name' => $this->data->name] : []),
            ...($this->data->email !== null ? ['email' => $this->data->email] : []),
        ];
    }

    public function createDtoFromResponse(Response $response): CustomerData
    {
        return CustomerData::fromSaloon($response->json());
    }
}
