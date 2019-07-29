<?php

namespace App\ProviderFactory;

use App\Provider\DiosProvider;
use App\Provider\Provider;
use App\MessageService;

class DiosFactory extends AbstractProviderFactory
{

    public function make(array $config): Provider
    {
        $providerConfig = $config[DiosProvider::class];
        return new DiosProvider($this->container->get(\GuzzleHttp\Client::class),$this->container->get(MessageService::class), $providerConfig['domain'],  $providerConfig['apiEndpoint']);
        


    }
}
