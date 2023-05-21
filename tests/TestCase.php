<?php

namespace Coderstm\Tests;

use Coderstm\Coderstm;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Coderstm\Tests\Fixtures\User;
use Coderstm\CoderstmServiceProvider;
use Coderstm\CoderstmEventServiceProvider;
use Coderstm\CoderstmPermissionsServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $apiKey = config('cashier.secret');

        if ($apiKey && !Str::startsWith($apiKey, 'sk_test_')) {
            throw new InvalidArgumentException('Tests may not be run with a production Stripe key.');
        }

        Coderstm::useUserModel(User::class);
    }

    protected function getPackageProviders($app)
    {
        return [
            CoderstmServiceProvider::class,
            CoderstmPermissionsServiceProvider::class,
            CoderstmEventServiceProvider::class,
        ];
    }
}
