<?php

declare(strict_types=1);

namespace Subster\PhpSdk\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Request\HasConnector;
use Subster\PhpSdk\DataObjects\CheckoutSessionStatusData;
use Subster\PhpSdk\SubsterConnector;

class CancelCheckoutSessionRequest extends Request
{
    use HasConnector;

    protected string $connector = SubsterConnector::class;

    protected Method $method = Method::DELETE;

    public function __construct(public readonly string $session) {}

    public function resolveEndpoint(): string
    {
        return "checkout/session/{$this->session}";
    }

    public function createDtoFromResponse(Response $response): CheckoutSessionStatusData
    {
        return CheckoutSessionStatusData::fromSaloon($response->json());
    }
}
