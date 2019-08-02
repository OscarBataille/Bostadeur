<?php

namespace App\Provider;

use App\Action\ActionExecutor;
use App\Entry\DiosEntry;
use GuzzleHttp\Client as HTTPClient;

class DiosProvider extends Provider
{

    /** Http client
     * @var GuzzleHttp\Client
     */
    private $client;

    private $domain;
    private $url;

    public function __construct(HTTPClient $client, ActionExecutor $actionExecutor, string $domain, string $url)
    {
        $this->client = $client;
        $this->domain = $domain;
        $this->url    = $url;

        parent::__construct($actionExecutor);

    }

    public function getAvailableEntries(): ProviderResult
    {
        $response = $this->client->request('GET', $this->domain . $this->url);

        $status = $response->getStatusCode(); // 200

        $body = (string) $response->getBody();

        $json = json_decode($body, JSON_OBJECT_AS_ARRAY);

        // Filter the city here
        $data = array_filter($json, function ($entry) {
            return preg_match('/ume(å|Å)/i', $entry['city']);
        });

        return (new ProviderResult())
            ->setStatus($status)
            ->setCount(count($data))
            ->setValue(array_map(function ($value) {
                return new DiosEntry($value);
            }, $data));

    }

}
