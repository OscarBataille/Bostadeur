<?php

ini_set('max_execution_time', 0);

// Autoload
require __DIR__ . '/vendor/autoload.php';

use App\AppCommand;
use App\MessageService;
use App\ProviderFactory\BalticgruppenFactory;
use App\ProviderFactory\DiosFactory;
use App\Provider\BalticgruppenProvider;
use App\Provider\DiosProvider;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;

// Config
$config  = require 'config.php';
$builder = new \DI\ContainerBuilder();
$builder->addDefinitions([
    MessageService::class        => function () use ($config) {
        return new MessageService($config['twilio']['sid'],
            $config['twilio']['token'],
            $config['twilio']['from'],
            $config['twilio']['to']);
    },
    AppCommand::class            => function (MessageService $messageService, ContainerInterface $container) use ($config) {
        //Make the providers
        $providers = [];
        foreach ($config['providers'] as $key => $value) {
            $providers[] = $container->get($key);
        }
        return new AppCommand($config, $messageService, $providers);
    },
    \GuzzleHttp\Client::class    => function () use ($config) {

        return new \GuzzleHttp\Client([
            'timeout' => 5.0,
        ]);
    },
    'config'                     => $config,
    BalticgruppenProvider::class => DI\factory([BalticgruppenFactory::class, 'create']),
    DiosProvider::class          => DI\factory([DiosFactory::class, 'create']),

]);

$container = $builder->build();

(function ($config, $container) {

    $application = new Application('SverigeBostad', '1.0');

    //App command
    $application->add($container->get(AppCommand::class));

    $application->setDefaultCommand('app:run', true);

    $application->run();

})($config, $container);
