<?php

ini_set('max_execution_time', 0);

// Autoload
require __DIR__ . '/vendor/autoload.php';


use App\AppCommand;
use App\MessageService;
use App\Provider\BalticgruppenProvider;
use App\Provider\DiosProvider;


use DI\ContainerBuilder;

use Symfony\Component\Console\Application;

// Config
$config  = require 'config.php';
$builder = new \DI\ContainerBuilder();
$builder->addDefinitions([
    MessageService::class     => function () use ($config) {
        return new MessageService($config['twilio']['sid'],
            $config['twilio']['token'],
            $config['twilio']['from'],
            $config['twilio']['to']);
    },
    AppCommand::class         => function (MessageService $messageService, DiosProvider $dios, BalticgruppenProvider $balticgruppen ) use ($config) {
        return new AppCommand($config, $messageService, [$dios, $balticgruppen]);
    },
    \GuzzleHttp\Client::class => function () use ($config) {

        return new \GuzzleHttp\Client([
            // Base URI is used with relative requests
            'base_uri' => $config['domain'],
            'timeout'  => 5.0,
        ]);
    },

]);

$container = $builder->build();

(function ($config, $container) {

    $application = new Application('Balticgruppen appartment fetcher', '1.0');

    //App command
    $application->add($container->get(AppCommand::class));

    $application->setDefaultCommand('app:run', true);

    $application->run();

})($config, $container);
