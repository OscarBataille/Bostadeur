<?php

namespace App\Provider;

use App\Entry\BalticgruppenEntry;
use App\MessageService;
use GuzzleHttp\Client as HTTPClient;

class BalticgruppenProvider extends Provider
{

    /**
     * Http client
     * @var GuzzleHttp\Client
     */
    private $client;

    private $endpoint;
    private $domain;

    public function __construct(HTTPClient $client, MessageService $message, string $domain, string $endpoint)
    {
        $this->client   = $client;
        $this->endpoint = $endpoint;
        $this->domain   = $domain;

        parent::__construct($message);
    }

    public function fetchAvailableResidence(): array
    {

        $response = $this->client->request('GET', $this->domain . $this->endpoint);

        $status = $response->getStatusCode(); // 200

        $body = (string) $response->getBody();

        $json = json_decode($body, JSON_OBJECT_AS_ARRAY);

        return [
            'status' => $status,
            'count'  => $json['@odata.count'] ?? 0,
            'data'   => $json['value'] ?? [],
        ];
    }

    /**
     * Fetch the data and return a ProviderResult object
     * @return ProviderResult
     */
    public function getAvailableEntries(): ProviderResult
    {
        $data = $this->fetchAvailableResidence();

        return (new ProviderResult())
            ->setStatus($data['status'])
            ->setCount($data['count'])
            ->setValue(array_map(function ($value) {
                return new BalticgruppenEntry($value, $this->domain);
            }, $data['data']));

    }

}
