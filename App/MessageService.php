<?php

namespace App;

use Twilio\Rest\Client;

class MessageService
{

    protected $sid;
    protected $authToken;
    protected $fromNumber;
    protected $toNumber;

    public function __construct(string $sid, string $authToken, string $fromNumber, string $toNumber)
    {
        $this->sid        = $sid;
        $this->authToken  = $authToken;
        $this->fromNumber = $fromNumber;
        $this->toNumber   = $toNumber;

    }

    public function send(string $message)
    {

        // Your Account SID and Auth Token from twilio.com/console

        $client = new Client($this->sid, $this->authToken);
        $client->messages->create(
            // Where to send a text message (your cell phone?)
            $this->toNumber,
            array(
                'from' => $this->fromNumber,
                'body' => $message,
            )
        );

    }
}
