<?php

namespace App\ProviderFactory;

use App\Provider\BalticgruppenProvider;
use App\Provider\Provider;
use App\MessageService;

class BalticgruppenFactory extends AbstractProviderFactory
{

    public function make(array $config): Provider
    {
        $providerConfig = $config[BalticgruppenProvider::class];
        return new BalticgruppenProvider($this->container->get(\GuzzleHttp\Client::class),$this->container->get(MessageService::class), $providerConfig['domain'],  $providerConfig['apiEndpoint']);

    }
}
