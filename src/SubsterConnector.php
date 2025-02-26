<?php

declare(strict_types=1);

namespace Subster\PhpSdk;

use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Traits\Plugins\AlwaysThrowOnErrors;
use Subster\PhpSdk\Resources\CheckoutSessionsResource;
use Subster\PhpSdk\Resources\CustomersResource;

class SubsterConnector extends Connector
{
    use AcceptsJson;
    use AlwaysThrowOnErrors;

    public function __construct(
        protected readonly string $token,
        protected readonly ?string $baseUrl = null,
    ) {}

    protected function defaultConfig(): array
    {
        return ['timeout' => 60 * 5];
    }

    protected function defaultAuth(): TokenAuthenticator
    {
        return new TokenAuthenticator($this->token);
    }

    public function resolveBaseUrl(): string
    {
        return $this->baseUrl ?? 'https://subster.ru/api/v1/';
    }

    public function customers(): CustomersResource
    {
        return new CustomersResource($this);
    }

    public function checkoutSessions(): CheckoutSessionsResource
    {
        return new CheckoutSessionsResource($this);
    }
}
