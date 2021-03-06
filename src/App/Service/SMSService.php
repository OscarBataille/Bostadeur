<?php
declare(strict_types=1);

namespace App\Service;

use Twilio\Rest\Client;

class SMSService
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

        $client  = new Client($this->sid, $this->authToken);
        $message = $client->messages->create(
            // Where to send a text message (your cell phone?)
            $this->toNumber,
            [
                'from' => $this->fromNumber,
                'body' => $message,
            ]
        );

        return $message->sid;
    }
}
