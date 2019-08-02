<?php

ini_set('max_execution_time', 0);

// Autoload
require __DIR__ . '/vendor/autoload.php';

use App\AppCommand;
use App\Service\SMSService;
use DI\ContainerBuilder;
use Dotenv\Exception\ValidationException;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use App\Action\ActionExecutor;

// Config
$config  = require 'config.php';
$builder = new \DI\ContainerBuilder();

$definitions = [
    SMSService::class     => function (\Dotenv\Dotenv $dotenv) use ($config) {
        try {
            $dotenv->required(['TWILIO_TOKEN', 'TWILIO_SID', 'TWILIO_TO', 'TWILIO_FROM']);

        } catch (ValidationException $e) {
            throw new Exception('The twilio configuration is missing in the .env file.');
        }
        return new SMSService(
            getenv('TWILIO_SID'),
            getenv('TWILIO_TOKEN'),
            getenv('TWILIO_FROM'),
            getenv('TWILIO_TO'));
    },
    AppCommand::class         => function (ContainerInterface $container) use ($config) {
        //Make the providers
        $providers = [];
        foreach ($config['providers'] as $key => $value) {
            $providers[] = $container->get($key);
        }
        return new AppCommand($config, $providers);
    },
    \GuzzleHttp\Client::class => function () use ($config) {

        return new \GuzzleHttp\Client([
            'timeout' => 5.0,
        ]);
    },
    \Dotenv\Dotenv::class     => function () {
        return \Dotenv\Dotenv::create(__DIR__);
    },
    ActionExecutor::class => function(ContainerInterface $container) use ($config){
        $actions = [];

        foreach ($config['actions'] as $action => $value) {
            $actions[] = $container->get($action);
        }

        return new ActionExecutor($actions);
    },
    'config'                  => $config,

];

// Add provider config
foreach ($config['providers'] as $provider => $value) {
    $definitions[$provider] = DI\factory([$value['factory'], 'create']);
}

// Add action config
foreach ($config['actions'] as $action => $value) {
    $definitions[$action] = DI\autowire()->constructorParameter('config', $value);
}

$builder->addDefinitions($definitions);
// DI config
$container = $builder->build();

//Load config
$container->get(\Dotenv\Dotenv::class)->load();

(function ($config, $container) {

    $application = new Application($config['name'], $config['version']);

    //App command
    $application->add($container->get(AppCommand::class));

    $application->setDefaultCommand('app:run', true);

    $application->run();

})($config, $container);
