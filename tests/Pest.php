<?php

declare(strict_types=1);

use Saloon\Config;
use Saloon\Http\Faking\MockClient;
use Subster\PhpSdk\Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

uses(TestCase::class)->in('.');

uses()
    ->beforeEach(fn () => MockClient::destroyGlobal())
    ->in(__DIR__);

Config::preventStrayRequests();
