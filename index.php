<?php

ini_set('max_execution_time', 0);

// Autoload
require __DIR__ . '/vendor/autoload.php';

use App\AppCommand;
use App\MessageService;
use DI\Container;
use Symfony\Component\Console\Application;

$container = new Container();

// Config
$config = require 'config.php';

(function ($config) {

    $application = new Application('Balticgruppen appartment fetcher', '1.0');

    // SMS service
    $message = new MessageService($config['twilio']['sid'],
        $config['twilio']['token'],
        $config['twilio']['from'],
        $config['twilio']['to']);

    //App command
    $application->add(new AppCommand($config, $message));

    $application->setDefaultCommand('app:run', true);
    
    $application->run();

})($config);
