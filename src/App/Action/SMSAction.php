<?php

namespace App\Action;

use App\Entry\EntryInterface;
use App\Provider\Provider;
use App\Service\SMSService;

class SMSAction extends ActionAbstract
{
    public function __construct(array $config, SMSService $smsService)
    {
        $this->smsService = $smsService;
        parent::__construct($config);
    }

    public function run(EntryInterface $object, Provider $provider)
    {
        // Send sms
        $this->smsService->send($provider->disponibilityStringGenerator($object));

    }
}
