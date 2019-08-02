<?php

namespace App\ProviderFactory;

use App\Provider\Provider;
use Psr\Container\ContainerInterface;

abstract class AbstractProviderFactory
{
    protected $providerConfig = [];

    /**
     * Container
     *
     * @var ContainerInterface
     */
    protected $container;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $config = $container->get('config');

        $this->providerConfig = $config['providers'];

    }

    public function create(): Provider
    {
        return $this->make($this->providerConfig);
    }

    /**
     * Make method, must be implemented.
     *
     * @param  array $config All the config.
     * @return Provider
     */
    abstract public function make(array $config): Provider;

}
